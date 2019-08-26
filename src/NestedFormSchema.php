<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Support\Facades\Request;
use JsonSerializable;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\Route;

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
    }

    /**
     * Get the fields for the current schema.
     */
    protected function fields()
    {

        $this->request->route()->setParameter('resource', $this->parentForm->resourceName);

        $fields = $this->filterFields()->map(function ($field) {
            $field->withMeta([
                'attribute' => $this->attribute($field->attribute)
            ]);

            if (!($field instanceof NestedForm)) {
                $field->withMeta([
                    'original_attribute' => $field->attribute
                ]);
            }

            $field->resolve($this->model);

            return $field->jsonSerialize();
        })->values();


        $this->request->route()->setParameter('resource', $this->parentForm->fromResource);

        return $fields;
    }

    /*
     * Turn an attribute into a nested attribute.
     */
    protected function attribute(string $attribute)
    {
        return $this->parentForm->attribute . '.' . $this->index .  '.' . $attribute;
    }

    /**
     * Get the current heading.
     */
    protected function heading()
    {
        $heading = isset($this->parentForm->heading) ? $this->parentForm->heading : $this->parentForm::wrapIndex() . '. ' . $this->parentForm->singularLabel;

        return str_replace($this->parentForm::wrapIndex(), $this->index, $heading);
    }

    /**
     * Return the method reflection to filter 
     * the fields.
     */
    protected function filterFields()
    {
        $method = new ReflectionMethod($this->parentForm->resourceClass, static::$filterMethod);

        $method->setAccessible(true);

        return $method->invoke($this->resourceInstance(), $this->request, collect($this->resourceInstance()->fields($this->request))
            ->reject(function ($field) {
                return $field instanceof BelongsTo && $field->resourceName === $this->parentForm->fromResource;
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
            'fields' => $this->fields(),
            'heading' => $this->heading(),
            'opened' => $this->parentForm->opened
        ];
    }
}
