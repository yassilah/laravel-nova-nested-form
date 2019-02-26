<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Yassi\NestedForm\Traits\HasHeading;

class NestedForm extends Field
{
    use HasHeading;

    /**
     * Constants for placeholders.
     */
    const INDEX = '{{index}}';
    const ATTRIBUTE = '__attribute';
    const ID = '__id';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nested-form';

    /**
     * The class name of the related resource.
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
     * The class name of the parent resource.
     *
     * @var string
     */
    public $parentResourceClass;

    /**
     * The URI key of the parent resource.
     *
     * @var string
     */
    public $parentResourceName;

    /**
     * The name of the Eloquent "has many" relationship.
     *
     * @var string
     */
    public $relationship;

    /**
     * The displayable singular label of the relation.
     *
     * @var string
     */
    public $singularLabel;

    /**
     * The original request.
     */
    protected $request;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->relationship = $this->attribute;
    }

    /**
     * Determine if the field should be displayed for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return call_user_func(
            [$this->resourceClass, 'authorizedToViewAny'], $request
        ) && parent::authorize($request);
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {

        return $this->withMeta([
            'children' => $this->getChildren($resource),
            'schema' => $this->getSchema($resource),
        ]);
    }

    /**
     * Get children.
     *
     * @param mixed $resource
     * @return array
     */
    protected function getChildren($resource)
    {
        return $resource->{$this->relationship}()->get()->map(function ($model, $index) {
            return (object) [
                'heading' => $this->getHeadingForIndex($index),
                'fields' => $this->getNestedResourcesFields($model, $index),
            ];
        });
    }

    /**
     * Get schema.
     *
     * @param mixed $resource
     * @return array
     */
    protected function getSchema($resource)
    {
        return (object) [
            'fields' => $this->getCreationFields($resource),
        ];
    }

    /**
     * Get nested resources' fields.
     *
     * @param Model $model
     * @param int $index
     * @return FieldCollection
     */
    protected function getNestedResourcesFields($model, $index)
    {
        return $this->setNestedHeadings(
            $this->setNestedAttributes(
                $this->setNestedRequests($model, $this->getUpdateFields($model)),
                $index),
            $index)->each->resolve($model);
    }

    /**
     * Get the creation schema for nested children.
     *
     * @param mixed $resource
     * @return FieldCollection
     */
    protected function getCreationSchema($resource)
    {
        return $this->getCreationFields($resource->{$this->relationship}()->getModel());
    }

    /**
     * Get the list of update fields for the given nested model.
     *
     * @param Model $model
     * @return FieldCollection
     */
    protected function getUpdateFields(Model $model)
    {
        return collect(Nova::newResourceFromModel($model)->fields($this->getRequest()));
    }

    /**
     * Set requests to children NestedForms.
     *
     * @param Model $model
     * @param Collection $fields
     * @return $fields
     */
    protected function setNestedRequests(Model $model, Collection $fields)
    {
        return $fields->map(function ($field) use ($model) {
            return $field instanceof NestedForm ? $field->setRequest($model) : $field;
        });
    }

    /**
     * Set the new nested attribute value to each child's fields.
     *
     * @param Collection $fields
     * @return Collection
     */
    protected function setNestedAttributes(Collection $fields, int $index = null)
    {
        return $fields->map(function ($field) use ($index) {
            return $field->withMeta([
                'originalAttribute' => $field->attribute,
                'attribute' => $this->getNestedAttribute() . ($this->isManyRelationship() ? '[' . $index . ']' : '') . '[' . $field->attribute . ']',
            ]);
        });
    }

    /**
     * Set the new nested heading to children NestedForms.
     *
     * @param Collection $fields
     * @return Collection
     */
    protected function setNestedHeadings(Collection $fields, int $index = null)
    {
        return $fields->map(function ($field) use ($index) {
            if ($field instanceof NestedForm) {
                $field->prefix = $this->prefix . (is_int($index) ? $index + 1 : $index) . $field->separator;
            }

            return $field;
        });
    }

    /**
     * Get the list of creation fields for the given nested model.
     *
     * @param Model $model
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function getCreationFields(Model $model)
    {
        return collect(Nova::newResourceFromModel($model)->fields($this->getRequest()));
    }

    /**
     * Get the nested field attribute.
     *
     * @return string
     */
    public function getNestedAttribute()
    {
        return $this->meta['attribute'] ?? $this->attribute;
    }

    /**
     * Get the relationship type.
     *
     * @return string
     */
    public function getRelationshipType()
    {
        return (new \ReflectionClass(Nova::modelInstanceForKey($this->getRequest()->resource)->{$this->relationship}()))->getShortName();
    }

    /**
     * Add the relationship type to the meta.
     *
     * @return string
     */
    public function addRelationshipType()
    {
        return $this->withMeta([
            Str::snake($this->getRelationshipType()) => true,
        ]);
    }

    /**
     * Add the relationship type to the meta.
     *
     * @return string
     */
    public function addIsManyRelationship()
    {
        return $this->withMeta([
            'is_many' => $this->isManyRelationship(),
        ]);
    }

    /**
     * Check whether the current relationship is
     * a Many or One relationship.
     *
     * @return bool
     */
    public function isManyRelationship()
    {
        return str_contains($this->getRelationshipType(), 'Many');
    }

    /**
     * Get the current request.
     *
     * @return NovaRequest
     */
    public function getRequest()
    {
        return $this->request ?? NovaRequest::createFrom(RequestFacade::instance());
    }

    /**
     * Set the current request.
     *
     * @param Model $model
     * @return self
     */
    public function setRequest(Model $model)
    {
        $oldRequest = $this->getRequest();

        $newRequest = NovaRequest::create(null, null, [
            'resource' => Nova::resourceForModel($model)::uriKey(),
            'id' => $model->id ?? null,
        ]);

        $this->request = NovaRequest::createFrom($newRequest, $oldRequest);

        return $this;
    }

    /**
     * Set the displayable singular label of the resource.
     *
     * @return string
     */
    public function singularLabel($singularLabel)
    {
        $this->singularLabel = $singularLabel;

        return $this;
    }

    /**
     * Get additional meta information to merge with the field payload.
     *
     * @return array
     */
    public function meta()
    {
        return array_merge([
            'resourceName' => $this->resourceName,
            'relationship' => $this->relationship,
            'singularLabel' => $this->singularLabel ?? Str::singular($this->name),
        ], $this->meta);
    }
}
