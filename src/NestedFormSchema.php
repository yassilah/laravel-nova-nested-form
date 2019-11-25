<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use ReflectionMethod;

class NestedFormSchema implements JsonSerializable
{
    /**
     * Parent form instance.
     * 
     * @var NestedForm
     */
    protected $parentForm;

    /**
     * Current model instance.
     * 
     * @var Model
     */
    protected $model;

    /**
     * Current index.
     * 
     * @var int|string
     */
    protected $index;

    /**
     * List of fields.
     */
    public $fields;

    /**
     * Name of the fields' fitler method.
     * 
     * @var string
     */
    protected static $filterMethod = 'removeNonCreationFields';

    /**
     * Create a new NestedFormSchema instance.
     */
    public function __construct(Model $model, $index, NestedForm $parentForm)
    {
        $this->model = $model;
        $this->index = $index;
        $this->parentForm = $parentForm;
        $this->request = app(NovaRequest::class);
        $this->fields = $this->fields();
    }

    /**
     * Get the fields for the current schema.
     */
    protected function fields()
    {

        $this->request->route()->setParameter('resource', $this->parentForm->resourceName);

        $fields = $this->filterFields()->map(function ($field) {
            if ($field instanceof NestedForm) {
                $field->attribute = $this->attribute($field->attribute);
            } else {
                $field->withMeta([
                    'attribute' => $this->attribute($field->attribute),
                    'originalAttribute' => $field->attribute
                ]);
            }

            $field->resolve($this->model);

            return $this->setComponent($field)->jsonSerialize();
        })->values();


        $this->request->route()->setParameter('resource', $this->parentForm->viaResource);

        return $fields;
    }

    /**
     * Set the custom component if need be.
     */
    protected function setComponent(Field $field)
    {
        if ($field instanceof BelongsTo) {
            $field->component = 'nested-form-belongs-to-field';
        } else if ($field instanceof File) {
            $field->component = 'nested-form-file-field';
        } else if ($field instanceof MorphTo) {
            $field->component = 'nested-form-morph-to-field';
        }

        return $field;
    }

    /*
     * Turn an attribute into a nested attribute.
     */
    protected function attribute(string $attribute = null)
    {
        return $this->parentForm->attribute . '[' . $this->index .  ']' . ($attribute ? '[' . $attribute . ']' : '');
    }

    /**
     * Get the current heading.
     */
    protected function heading()
    {
        $heading = isset($this->parentForm->heading) ? $this->parentForm->heading : $this->defaultHeading();

        return str_replace($this->parentForm::wrapIndex(), $this->index, $heading);
    }

    /**
     * Default heading.
     */
    protected function defaultHeading()
    {
        return $this->parentForm::wrapIndex() . $this->parentForm->separator . ' ' . $this->parentForm->singularLabel;
    }

    /**
     * Return the method reflection to filter 
     * the fields.
     */
    protected function filterFields()
    {
        $method = new ReflectionMethod($this->parentForm->resourceClass, static::$filterMethod);

        $method->setAccessible(true);

        return $method->invoke($this->resourceInstance(), $this->request, $this->resourceInstance()->availableFields($this->request)
            ->reject(function ($field) {
                return $this->parentForm->isRelatedField($field);
            })->map(function ($field) {
                if ($field instanceof Panel) {
                    return collect($field->data)->map(function ($field) {
                        $field->panel = null;
                        return $field;
                    })->values();
                }

                return $field;
            })->flatten());
    }

    /**
     * Return an instance of the nested form resource class.
     */
    protected function resourceInstance()
    {
        return new $this->parentForm->resourceClass($this->model);
    }

    /**
     * Create a new NestedFormSchema instance.
     */
    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'fields' => $this->fields,
            'heading' => $this->heading(),
            'opened' => $this->parentForm->opened,
            'attribute' => $this->attribute()
        ];
    }
}
