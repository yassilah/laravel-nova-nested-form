<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JsonSerializable;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class NestedFormChild implements JsonSerializable
{

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
     * Heading.
     *
     * @var string
     */
    protected $heading;

    /**
     * Opened.
     *
     * @var bool
     */
    protected $opened;

    /**
     * Attributes transformation classes.
     * 
     * @var array
     */
    protected $attributesTransformations;

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

        $request = app(NovaRequest::class);

        $this->fields = Nova::newResourceFromModel($this->model)->updateFields($request)->filter(function ($field) use ($request) {
            if ($field instanceof BelongsTo && $field->resourceName === $request->resource) {
                return false;
            }

            return true;
        });

        $this->heading('')->open(true)->resolve();
    }

    /**
     * Resolve the fields.
     *
     * @return  self
     */
    protected function resolve()
    {

        $this->recursivelyTransformAttributes($this->fields);

        foreach ($this->fields as $field) {
            if ($this->isRelational($field)) {
                $this->model->setRelation($field->attribute, $this->model->{$field->originalAttribute});
            }

            $field->resolve($this->model, $field->originalAttribute);
        }

        return $this;
    }

    /**
     * Wheck whether the given field is a
     * relational field.
     *
     * @param  Field  $field
     * @return  bool
     */
    protected function isRelational(Field $field)
    {
        return $field instanceof BelongsTo ||
            $field instanceof HasOne ||
            $field instanceof HasMany ||
            $field instanceof MorphOne ||
            $field instanceof MorphMany ||
            $field instanceof BelongsToMany;
    }

    /**
     * Recursively transform attributes.
     *
     * @param  FieldCollection  $fields
     * @return  self
     */
    protected function recursivelyTransformAttributes(FieldCollection $fields)
    {
        if (!$this->attributesTransformations) {
            $this->storeAttributesTransformations();
        }

        $fields->each(function ($field) {
            $field->originalAttribute = $field->attribute;

            $field->attribute = $this->getTransformedAttribute($field->attribute);

            foreach ($this->attributesTransformations as $className) {
                if (str_contains(new \ReflectionClass($field), $className)) {
                    $className::transformAttributesOf($field, $this);
                }
            }
        });
    }

    /**
     * Get the transformed attribute.
     *
     * @param  string  $attribute
     * @return  string
     */
    protected function getTransformedAttribute(string $attribute = null)
    {
        return $this->parent->attribute . NestedForm::SEPARATOR . $this->index . ($attribute ? NestedForm::SEPARATOR . $attribute : '');
    }

    /**
     * Store and require the available attributes 
     * transformations.
     * 
     * @return  array
     */
    protected function storeAttributesTransformations()
    {
        $this->attributesTransformations = [];

        foreach (glob(__DIR__ . '/AttributesTransformations/*.php') as $filename) {
            require $filename;

            $this->attributesTransformations[] = basename($filename);
        }

        return $this->attributesTransformations;
    }

    /**
     * Transform layout for serialization
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'fields' => $this->fields->values(),
            'heading' => $this->heading,
            'opened' => $this->opened,
            'attribute' => $this->getTransformedAttribute(),
            NestedForm::ID => $this->model->id,
        ];
    }

    /**
     * Set heading.
     *
     * @param  string  $heading
     * @return  self
     */
    public function heading(string $heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * Set opened.
     *
     * @param  bool|string  $opened
     * @return  self
     */
    public function open($opened)
    {
        $this->opened = $opened;

        return $this;
    }
}
