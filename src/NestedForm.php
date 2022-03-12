<?php

namespace Yassi\NestedForm;

use function GuzzleHttp\json_encode;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Contracts\RelatableField;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Controllers\ResourceDestroyController;
use Laravel\Nova\Http\Controllers\ResourceDetachController;
use Laravel\Nova\Http\Controllers\ResourceStoreController;
use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;
use Laravel\Nova\Http\Requests\DetachResourceRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;
use Laravel\Nova\Nova;
use Illuminate\Contracts\Validation\Rule;
use Laravel\Nova\Panel;

class NestedForm extends Field implements RelatableField
{

    /**
     * Wrap left.
     *
     * @var string
     */
    const WRAP_LEFT = '{{';

    /**
     * Wrap right.
     *
     * @var string
     */
    const WRAP_RIGHT = '}}';

    /**
     * INDEX.
     *
     * @var string
     */
    const INDEX = 'INDEX';

    /**
     * ID.
     *
     * @var string
     */
    const ID = 'ID';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nested-form';

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var \Closure|bool
     */
    public $showOnIndex = false;

    /**
     * Indicates if the element should be shown on the detail view.
     *
     * @var \Closure|bool
     */
    public $showOnDetail = false;

    /**
     * The field's relationship resource class.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The field's relationship resource name.
     *
     * @var string
     */
    public $resourceName;

    /**
     * The field's relationship name.
     *
     * @var string
     */
    public $viaRelationship;

    /**
     * The field's singular label.
     *
     * @var string
     */
    public $singularLabel;

    /**
     * The field's plural label.
     *
     * @var string
     */
    public $pluralLabel;

    /**
     * Default separator.
     *
     * @var string
     */
    public $separator = '.';

    /**
     * From resource uriKey.
     *
     * @var string
     */
    public $viaResource;

    /**
     * Key name.
     *
     * @var string
     */
    public $keyName;


    /**
     * Whether the form should be opened by default.
     *
     * @var boolean
     */
    public $opened = true;

    /**
     * The heading template for children.
     *
     * @var string
     */
    public $heading;

    /**
     * The maximum number of children.
     *
     * @var int
     */
    public $max = 0;

    /**
     * The minimum number of children.
     *
     * @var int
     */
    public $min = 0;

    /**
     * Condition to display the nested form.
     */
    public $displayIfCallback;

    /**
     * Return context
     *
     * @var Panel|Field|NestedForm
     */
    protected $returnContext;

    /**
     * Create a new nested form.
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
        $this->singularLabel = method_exists($this->resourceClass, 'singularLabel') ? $this->resourceClass::singularLabel() : Str::singular($this->name);
        $this->pluralLabel = method_exists($this->resourceClass, 'label') ? $this->resourceClass::label() : Str::singular($this->name);
        $this->keyName = (new $this->resourceClass::$model)->getKeyName();
        $this->viaResource = app(NovaRequest::class)->route('resource');
        $this->returnContext = $this;

        // Nova ^3.3.x need this to fix cannot add relation on create mode
        if(get_class(app(NovaRequest::class)) !== "Laravel\Nova\Http\Requests\GlobalSearchRequest")
            $this->resolve(app(NovaRequest::class)->model());
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
            'children' => $this->children($resource),
            'schema' => $this->schema($resource),
            'viaResourceId' => $resource->{$resource->getKeyName()},
        ]);
    }

    /**
     * Get the schema.
     */
    public function schema($resource)
    {
        if (method_exists($resource, $this->viaRelationship)) {
            return NestedFormSchema::make($resource->{$this->viaRelationship}()->getModel(), static::wrapIndex(), $this);
        }
        return false;
    }

    /**
     * Get the children.
     */
    public function children($resource)
    {
        if (method_exists($resource, $this->viaRelationship)) {
            return $resource->{$this->viaRelationship}()->get()->map(function ($model, $index) {
                return NestedFormChild::make($model, $index, $this);
            })->all();
        }

        return false;
    }

    /**
     * Set the heading.
     *
     * @param string $heading
     */
    public function heading(string $heading)
    {
        $this->heading = $heading;

        return $this->returnContext;
    }

    /**
     * Set whether the form should be opened by default.
     *
     * @param boolean $opened
     */
    public function open(bool $opened)
    {
        $this->opened = $opened;

        return $this->returnContext;
    }

    /**
     * Set the default separator.
     *
     * @param string $separator
     */
    public function separator(string $separator)
    {
        $this->separator = $separator;

        return $this->returnContext;
    }

    /**
     * Set the maximum number of children.
     */
    public function max(int $max)
    {
        $this->max = $max;

        return $this->returnContext;
    }

    /**
     * Set the minimum number of children.
     */
    public function min(int $min)
    {
        $this->min = $min;

        return $this->returnContext;
    }

    /**
     * Set custom validation rules.
     */
    public function rules($rules)
    {
        parent::rules(($rules instanceof Rule || is_string($rules)) ? func_get_args() : $rules);

        return $this->returnContext;
    }

    /**
     * Set the condition to display the form.
     */
    public function displayIf(\Closure $displayIfCallback)
    {
        $this->displayIfCallback = function () use ($displayIfCallback) {
            return collect(call_user_func($displayIfCallback, $this, app(Novarequest::class)))->map(function ($condition) {
                if (isset($condition['attribute'])) {
                    $condition['attribute'] = static::conditional($condition['attribute']);
                }

                return $condition;
            });
        };

        return $this->returnContext;
    }

    /**
     * Get the relationship type.
     */
    protected function getRelationshipType()
    {
        return (new \ReflectionClass(Nova::modelInstanceForKey($this->viaResource)->{$this->viaRelationship}()))->getShortName();
    }

    /**
     * Whether the current relationship if many or one.
     */
    protected function isManyRelationsip()
    {
        return Str::contains($this->getRelationshipType(), 'Many');
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
            $newRequest = NovaRequest::createFrom($request);
            if (!$model->{$model->getKeyName()} && $request->has($model->getKeyName())) {
                $model->{$model->getKeyName()} = $request->get($model->getKeyName());
            }
            $children = collect($newRequest->get($requestAttribute));
            $newRequest->route()->setParameter('resource', $this->resourceName);
            $this->deleteChildren($newRequest, $model, $children);
            $this->createOrUpdateChildren($newRequest, $model, $children, $requestAttribute, $this->getRelatedKeys($newRequest));
        } else {
            $model::saved(function ($model) use ($request, $requestAttribute, $attribute) {
                $this->fillAttributeFromRequest($request, $requestAttribute, $model, $attribute);
            });
        }
    }

    /**
     * Reject related fields.
     */
    public function isRelatedField($field)
    {
        if ($field instanceof BelongsTo || $field instanceof BelongsToMany) {
            return $field->resourceName === $this->viaResource;
        } else if ($field instanceof MorphTo) {
            return collect($field->morphToTypes)->pluck('value')->contains($this->viaResource);
        }

        return false;
    }

    /**
     * Get the related key name for filling the attribute.
     */
    protected function getRelatedKeys(NovaRequest $request)
    {
        $field = collect(Nova::resourceInstanceForKey($this->resourceName)->fields($request))->first(function ($field) {
            return $this->isRelatedField($field);
        });

        if (!$field) {
            throw new \Exception(__('A field defining the inverse relationship needs to be set on your related resource (e.g. MorphTo, BelongsTo, BelongsToMany...)'));
        }

        if ($field instanceof MorphTo) {
            return [$field->attribute => self::ID, $field->attribute . '_type' => $this->viaResource];
        }

        return [$field->attribute => self::ID];
    }

    /**
     * Throw validation exception with correct attributes.
     */
    protected function throwValidationException(ValidationException $exception, int $index)
    {
        throw $exception::withMessages($this->getValidationErrors($exception, $index));
    }

    /**
     * Get validation errors with correct attributes.
     */
    protected function getValidationErrors(ValidationException $exception, int $index)
    {
        return collect($exception->errors())->mapWithKeys(function ($value, $key) use ($index) {
            return [$this->getValidationErrorAttribute($index, $key) => $value];
        })->toArray();
    }

    /**
     * Get validation error attribute.
     */
    protected function getValidationErrorAttribute(int $index, string $key)
    {
        return preg_replace('/(?<=\])((?!\[).+?(?!\]))(?=\[|$)/', '[$1]', $this->attribute . '[' . $index . ']' . $key);
    }

    /**
     * Delete the children not sent through the request.
     */
    protected function deleteChildren(NovaRequest $request, $model, $children)
    {
        if ($this->getRelationshipType() === 'BelongsToMany') {
            return (new ResourceDetachController)->handle($this->getDetachRequest($request, $model, $children));
        }

        return (new ResourceDestroyController)->handle($this->getDeleteRequest($request, $model, $children));
    }

    /**
     * Create or update the children sent through the request.
     */
    protected function createOrUpdateChildren(NovaRequest $request, $model, $children, $requestAttribute, $relatedKeys)
    {
        $children->each(function ($child, $index) use ($request, $model, $requestAttribute, $relatedKeys) {
            try {
                if (isset($child[$this->keyName])) {
                    return $this->updateChild($request, $model, $child, $index, $requestAttribute, $relatedKeys);
                }

                return $this->createChild($request, $model, $child, $index, $requestAttribute, $relatedKeys);
            } catch (ValidationException $exception) {
                $this->throwValidationException($exception, $index);
            }
        });
    }

    /**
     * Create the child sent through the request.
     */
    protected function createChild(NovaRequest $request, $model, $child, $index, $requestAttribute, $relatedKeys)
    {
        return (new ResourceStoreController)->handle($this->getCreateRequest($request, $model, $child, $index, $requestAttribute, $relatedKeys));
    }

    /**
     * Update the child sent through the request.
     */
    protected function updateChild(NovaRequest $request, $model, $child, $index, $requestAttribute, $relatedKeys)
    {
        return (new ResourceUpdateController)->handle($this->getUpdateRequest($request, $model, $child, $index, $requestAttribute, $relatedKeys));
    }

    /**
     * Get a request for detach.
     */
    protected function getDetachRequest(NovaRequest $request, $model, $children)
    {
        return DetachResourceRequest::createFrom($request->replace([
            'viaResource' => $this->viaResource,
            'viaResourceId' => $model->getKey(),
            'viaRelationship' => $this->viaRelationship,
            'resources' => $model->{$this->viaRelationship}()->select($this->attribute . '.' . $this->keyName)->whereNotIn($this->attribute . '.' . $this->keyName, $children->pluck($this->keyName))->pluck($this->keyName)
        ]));
    }

    /**
     * Get a request for delete.
     */
    protected function getDeleteRequest(NovaRequest $request, $model, $children)
    {
        return DeleteResourceRequest::createFrom($request->replace([
            'viaResource' => null,
            'viaResourceId' => null,
            'viaRelationship' => null,
            'resources' => $model->{$this->viaRelationship}()->whereNotIn($this->keyName, $children->pluck($this->keyName))->pluck($this->keyName)
        ]));
    }

    /**
     * Get a request for create.
     */
    protected function getCreateRequest(NovaRequest $request, $model, $child, $index, $requestAttribute, $relatedKeys)
    {
        $createRequest = CreateResourceRequest::createFrom($request->replace([
            'viaResource' => $this->viaResource,
            'viaResourceId' => $model->getKey(),
            'viaRelationship' => $this->viaRelationship
        ])->merge($child)->merge(collect($relatedKeys)->map(function ($value) use ($model) {
            return $value === self::ID ? $model->getKey() : $value;
        })->toArray()));

        $createRequest->files = collect($request->file($requestAttribute . '.' . $index));

        return $createRequest;
    }

    /**
     * Get a request for update.
     */
    protected function getUpdateRequest(NovaRequest $request, $model, $child, $index, $requestAttribute, $relatedKeys)
    {
        return UpdateResourceRequest::createFrom($this->getCreateRequest($request, $model, $child, $index, $requestAttribute, $relatedKeys)->merge([
            'resourceId' => $child[$this->keyName]
        ]));
    }

    /**
     * Set the panel instance.
     */
    public function asPanel(Panel $panel)
    {
        $this->returnContext = $panel;
    }

    /**
     * Set the field instance.
     */
    public function asField(Field $field)
    {
        $this->returnContext = $field;
    }

    /**
     * Create a new NestedForm instance.
     */
    public static function make(...$arguments)
    {
        return new NestedFormPanel(new static(...$arguments));
    }

    /**
     * Wrap an attribute into a dynamic attribute
     * value.
     *
     * @param string $attribute
     * @param string $default
     */
    public static function wrapAttribute(string $attribute, $default = '')
    {
        return self::WRAP_LEFT . $attribute . '|' . $default . self::WRAP_RIGHT;
    }

    /**
     * Turn a given attribute string into
     * a conditional attribute.
     *
     * @param string $attribute
     */
    public static function conditional(string $attribute)
    {
        return preg_replace('/\.(.*?)(?=\.|$)/', '\[$1\]', preg_replace('/\.\$\./', '.' . static::wrapIndex() . '.', preg_replace('/\.\*\./', '\.[0-9]+\.', $attribute)));
    }

    /**
     * Wrap the index key.
     */
    public static function wrapIndex()
    {
        return self::WRAP_LEFT . self::INDEX . self::WRAP_RIGHT;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'singularLabel' => $this->singularLabel,
                'pluralLabel' => $this->pluralLabel,
                'indexKey' => static::wrapIndex(),
                'wrapLeft' => self::WRAP_LEFT,
                'wrapRight' => self::WRAP_RIGHT,
                'resourceName' => $this->resourceName,
                'viaRelationship' => $this->viaRelationship,
                'viaResource' => $this->viaResource,
                'keyName' => $this->keyName,
                'min' => $this->min,
                'max' => $this->isManyRelationsip() ? $this->max : 1,
                'displayIf' => isset($this->displayIfCallback) ? call_user_func($this->displayIfCallback) : null
            ]
        );
    }
}
