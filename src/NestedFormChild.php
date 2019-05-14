<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Yassi\NestedForm\AttributesTransformations\CanTransformAttributes;

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
     * Set list of fields.
     * 
     * @return  FieldCollection
     */
    protected function setFields(NovaRequest $request)
    {
        $this->fields = Nova::newResourceFromModel($this->model)->updateFields($request)->filter(function ($field) use ($request) {
            if ($field instanceof BelongsTo && $field->resourceName === $request->resource) {
                return false;
            }

            return true;
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
            'fields' => $this->recursivelyTransformAttributes($this->fields),
            'heading' => $this->getHeading()
        ];
    }
}
