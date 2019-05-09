<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResolvesReverseRelation;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Controllers\ResourceStoreController;
use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;
use Laravel\Nova\Nova;
use Nexmo\Client\Exception\Request;

class NestedForm extends Field
{

    use ResolvesReverseRelation;

    /**
     * Index key.
     *
     * @var  string
     */
    const INDEX = '{{index}}';

    /**
     * ID key.
     *
     * @var  string
     */
    const ID = 'id';

    /**
     * Variable eparator.
     *
     * @var  string
     */
    const SEPARATOR = '__';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nested-form';

    /**
     * The class of the related resource.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The URI key of the related resource.
     *
     * @var string
     */
    public $resourceName;

    /**
     * The name of the Eloquent relationship.
     *
     * @var string
     */
    public $viaRelationship;

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * Indicates if the element should be shown on the detail view.
     *
     * @var bool
     */
    public $showOnDetail = false;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct(string $name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->viaRelationship = $this->attribute;
    }

    /**
     * Resolve the form fields.
     *
     * @param $resource
     * @param $attribute
     *
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        $this->withMeta([
            'children' => $this->getChildren($resource),
            'viaRelationship' => $this->viaRelationship,
            'resourceName' => $this->resourceName,
            'relatedResourceName' => Nova::resourceForModel($resource)::uriKey(),
            'relatedResourceId' => $resource->id,
            'is_many_relationship' => $this->isManyRelationship($resource),
            'schema' => $this->getSchema($resource),
            'INDEX' => self::INDEX,
            'ID' => self::ID,
            'SEPARATOR' => self::SEPARATOR,
        ]);
    }

    /**
     * Fills the attributes of the model within the container if the dependencies for the container are satisfied.
     *
     * @param NovaRequest $request
     * @param string $requestAttribute
     * @param object $model
     * @param string $attribute
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($model->exists) {

            $data = $this->getCurrentInput($request) ?? [];

            foreach ($data as $item) {
                $newRequest = isset($item[self::ID]) ?
                    app(UpdateResourceRequest::class) : app(CreateResourceRequest::class);

                $controller = isset($item[self::ID]) ?
                    app(ResourceUpdateController::class) : app(ResourceStoreController::class);

                $input = array_merge([
                    '_method' => $request->get('_method'),
                    '_retrieved_at' => $request->get('_retrieved_at'),
                    'resource' => $this->resourceName,
                    'resourceId' => $item[self::ID] ?? null,
                    'viaResource' => $request->resource,
                    'viaResourceId' => $model->id,
                    'viaRelationship' => $this->viaRelationship,
                    'isNestedRequest' => true,
                ], $item);

                $newRequest->replace($input);
                $controller->handle($newRequest);
            }
        } else {
            $model::saved(function ($model) use ($request, $requestAttribute, $attribute) {
                $this->fillAttributeFromRequest($request, $requestAttribute, $model, $attribute);
            });
        }
    }

    /**
     * Get the validation rules for this field.
     *
     * @param  NovaRequest  $request
     * @param  string  $attribute
     * @return array
     */
    public function getRules(NovaRequest $request, string $attribute = null)
    {
        $rules = [];

        if (!$request->isNestedRequest) {
            $model = $this->resourceClass::newModel();
            $model::unguard();
            $children = $this->makeChildren(Collection::make($this->getCurrentInput($request, $attribute))->mapInto($model));

            $rules = $children->flatMap->fields->flatMap(function ($field) use ($request) {
                return $field->getRules($request, $field->attribute);
            })->toArray();
        }

        return $rules;
    }

    /**
     * Handle any post-validation processing.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    protected static function afterValidation(NovaRequest $request, $validator)
    {
        throw new \Exception('salut');
    }

    /**
     * Check whether the current relationship
     * can have many children.
     *
     * @param  Model  $resource
     * @return  bool
     */
    protected function isManyRelationship(Model $resource)
    {
        return str_contains((new \ReflectionClass($resource->{$this->viaRelationship}()))->getShortName(), 'Many');
    }

    /**
     * Get index from request query.
     *
     * @return  int
     */
    protected function getIndexFromRequest()
    {
        return preg_replace('/.+\[([0-9+])\].+/', '$1', app(NovaRequest::class)->field);
    }

    /**
     * Get children resources.
     *
     * @param  Model  $resource
     * @return  NestedFormChildren
     */
    public function getChildren(Model $resource)
    {
        if ($resource->exists) {
            return $this->makeChildren($resource->{$this->viaRelationship}()->get());
        } else {
            return $this->makeChildren(new Collection($resource->{$this->viaRelationship}()->getRelated()));
        }
    }

    /**
     * Create the list of nested form children
     * with the given children attributes.
     *
     * @param  Collection  $models
     * @return  NestedFormChildren
     */
    protected function makeChildren(Collection $models)
    {
        return new NestedFormChildren($models->map(function ($model, $index) {
            return new NestedFormChild($model, $index, $this);
        })->all());
    }

    /**
     * Get schema resources.
     *
     * @param  Model  $resource
     * @return  NestedFormChildren
     */
    public function getSchema(Model $resource)
    {
        return (new NestedFormChild($resource->{$this->viaRelationship}()->getRelated(), self::INDEX, $this));
    }

    /**
     * Get current input data in the
     * given request.
     *
     * @param  NovaRequest  $request
     * @param  string|null  $attribute
     * @return  array
     */
    private function getCurrentInput(NovaRequest $request, string $attribute = null)
    {
        return Arr::get($this->separatorToArray($request->all()), $this->separatedKeyToDotKey($attribute ?? $this->attribute));
    }

    /**
     * Recursively transform a given
     * array with separated keys
     * into a proper array.
     *
     * @param  array  $content
     * @return  array
     */
    private function separatorToArray(array $array)
    {
        $newArray = [];

        $keys = array_keys($array);

        foreach ($keys as $key) {
            Arr::set($newArray, $this->separatedKeyToDotKey($key), $array[$key]);
        }

        return $newArray;
    }

    /**
     * Replace separated key to dotted key.
     *
     * @param  string  $separated
     * @return  string
     */
    private function separatedKeyToDotKey(string $separated)
    {
        return str_replace(self::SEPARATOR, '.', $separated);
    }

    /**
     * Create a new instance of NestedForm.
     */
    public static function make(...$arguments)
    {
        $instance = new static(...$arguments);

        if (str_contains(Route::currentRouteAction(), ['FieldDestroyController', 'AssociatableController'])) {
            $request = app(NovaRequest::class);

            $fakeModel = Nova::resourceForKey($request->resource)::newModel();

            $model = $fakeModel->find($request->resourceId) ?? $fakeModel;

            if (method_exists($model, $instance->viaRelationship)) {
                return $instance->getChildren($model)->findField($request->field);
            }
        }

        return $instance;
    }
}
