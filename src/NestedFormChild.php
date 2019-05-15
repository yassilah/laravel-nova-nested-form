<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Yassi\NestedForm\AttributesTransformations\CanTransformAttributes;
use Laravel\Nova\Fields\BelongsTo;

class NestedFormChild implements JsonSerializable
{
    use CanTransformAttributes;

    /**
     * List of fields.
     *
     * @var FieldCollection
     */
    public $fields;

    /**
     * Current model instance.
     *
     * @var Model
     */
    protected $model;

    /**
     * Parent form.
     *
     * @var NestedForm
     */
    protected $parent;

    /**
     * Current index.
     *
     * @var int
     */
    protected $index;

    /**
     * Create a new child.
     *
     * @param  Model  $model
     * @param  int|string  $index
     * @param  NestedForm  $parent
     */
    public function __construct(Model $model, $index, NestedForm $parent)
    {
        $this->model = $model;
        $this->parent = $parent;
        $this->index = $index;
        $this->setFields(app(NovaRequest::class));
    }

    /**
     * Get transformed attributes.
     * 
     * @return  self
     */
    public function getFields()
    {
        return $this->recursivelyTransformAttributes($this->fields);
    }


    /**
     * Set list of fields.
     * 
     * @return  FieldCollection
     */
    protected function setFields(NovaRequest $request)
    {
        $this->fields = Nova::newResourceFromModel($this->model)
            /**
             * Call either the updateFields or creationFields
             * method depending on whether the model exists.
             */
            ->{$this->model->exists ? 'updateFields' : 'creationFields'}($request)
            /**
             * Remove the BelongsTo fields which correspond to the 
             * parent resource.
             */
            ->reject(function ($field) use ($request) {
                return $field instanceof BelongsTo && $request->newResource()->availableFields($request)->findFieldByAttribute($this->parent->attribute);
            });

        return $this;
    }

    /**
     * Get opened.
     * 
     * @return  bool
     */
    protected function getOpened()
    {
        return $this->parent->opened === 'only first' && $this->index === 0 || $this->parent->opened === true;
    }

    /**
     * Get heading.
     * 
     * @return  string
     */
    protected function getHeading()
    {
        return $this->parent->makeHeadingForIndex($this->index);
    }

    /**
     * Transform layout for serialization
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'opened' => $this->getOpened(),
            'fields' => $this->getFields(),
            'heading' => $this->getHeading(),
            'resourceName' => $this->parent->resourceName
        ];
    }
}
