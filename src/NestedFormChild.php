<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Fields\BelongsTo;
use Yassi\NestedForm\PackageExtensions\HasPackageExtensions;
use Laravel\Nova\Fields\ID;

class NestedFormChild implements JsonSerializable
{
    use HasPackageExtensions;

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
        return new NestedFormFields($this->recursivelyTransformAttributes($this->fields), $this->parent);
    }

    /**
     * Recursively transform attributes.
     *
     * @param  FieldCollection  $fields
     * @return  self
     */
    public function recursivelyTransformAttributes(FieldCollection $fields)
    {
        $fields->each(function (&$field) {
            if ($field instanceof NestedForm) {
                $field->setIsInSchema(!$this->model->exists)->preprendToHeadingPrefix($this->parent->makeHeadingPrefixForIndex($this->index));
            }

            $field->withMeta([
                'originalAttribute' => $field->attribute
            ]);

            $field->attribute = $this->getTransformedAttribute($field->attribute);

            $this->transformAttributesUsingPackageExtensions($field);

            return $field;
        });

        return $fields;
    }

    /**
     * Get the transformed attribute.
     *
     * @param  string  $attribute
     * @return  string
     */
    public function getTransformedAttribute(string $attribute = null)
    {
        return $this->parent->attribute . NestedForm::SEPARATOR . $this->index . ($attribute ? NestedForm::SEPARATOR . $attribute : '');
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
            'fields' => $this->getFields()->values(),
            'heading' => $this->getHeading(),
            'resourceName' => $this->parent->resourceName,
            NestedForm::ID => $this->model->id,
            'attribute' => $this->getTransformedAttribute()
        ];
    }
}
