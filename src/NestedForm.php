<?php

namespace Yassi\NestedForm;

use App\Nova\Resource;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Panel;

class NestedForm extends Field
{



    /**
     * INDEX.
     * 
     * @var string
     */
    const INDEX = '{{ INDEX }}';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nested-form';

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
     * From resource uriKey.
     * 
     * @var string
     */
    public $fromResource;

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
        $this->singularLabel = Str::singular($this->name);
        $this->pluralLabel = Str::plural($this->name);

        $request = app(NovaRequest::class);

        $this->fromResource = $request->route('resource');
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
            'children' => $resource->{$this->viaRelationship}()->get()->map(function ($model, $index) {
                return NestedFormChild::make($model, $index, $this);
            })->all(),
            'schema' => NestedFormSchema::make($resource->{$this->viaRelationship}()->getModel(), self::INDEX, $this)
        ]);
    }

    /**
     * Set the heading.
     * 
     * @param string $heading
     */
    public function heading(string $heading)
    {
        $this->heading = $heading;
    }

    /**
     * Set whether the form should be opened by default.
     * 
     * @param boolean $opened
     */
    public function opened(boolean $opened)
    {
        $this->opened = $opened;
    }


    /**
     * Create a new element.
     *
     * @return static
     */
    public static function make(...$arguments)
    {
        $instance = new static(...$arguments);

        return new Panel($instance->name, [
            $instance
        ]);
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
                'indexKey' => static::INDEX
            ],
        );
    }
}
