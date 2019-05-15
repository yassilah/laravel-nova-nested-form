<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Nova;
use ReflectionClass;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Illuminate\Support\Facades\Route;

class NestedForm extends Field
{
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
     * The related model instance.
     *
     * @var Model
     */
    public $relatedResource;

    /**
     * The name of the Eloquent relationship.
     *
     * @var string
     */
    public $viaRelationship;

    /**
     * Heading.
     *
     * @var string
     */
    protected $heading;

    /**
     * Heading separator.
     *
     * @var string
     */
    protected $headingSeparator = '.';

    /**
     * Current heading prefix.
     * 
     * @var string
     */
    protected $headingPrefix = self::INDEX;

    /**
     * Opened.
     *
     * @var bool
     */
    public $opened = false;

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
        $this->relatedResource = $resource;
        $this->attribute = $attribute ?? $this->attribute;

        return $this;
    }

    /**
     * Get the list of children.
     * 
     * @return  NestedFormChildren
     */
    protected function getChildren()
    {
        return $this->relatedResource->{$this->viaRelationship}()->get()->map(function ($item, $index) {
            return new NestedFormChild($item, $index, $this);
        });
    }

    /**
     * Get the schema.
     * 
     * @return  NestedFormChildren
     */
    public function getSchema()
    {
        return new NestedFormChild(Nova::modelInstanceForKey($this->resourceName), self::INDEX, $this);
    }

    /**
     * Get the relationship type.
     * 
     * @return  string
     */
    protected function getRelationshipType($resource)
    {
        return (new ReflectionClass($resource->{$this->viaRelationship}()))->getShortName();
    }

    /**
     * Whether the current relationship is a -to-many relationship.
     * 
     * @return  bool
     */
    protected function isManyRelationship()
    {
        return Str::contains($this->getRelationshipType($this->relatedResource), 'Many');
    }

    /**
     * Get the plural label of the current form.
     * 
     * @return  string
     */
    public function getPluralLabel()
    {
        return Str::plural(Str::title($this->resourceName));
    }

    /**
     * Get the singular label of the current form.
     * 
     * @return  string
     */
    public function getSingularLabel()
    {
        return Str::singular(Str::title($this->resourceName));
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
     * Set heading prefix.
     * 
     * @param  string  $headingPrefix
     * @return  self
     */
    public function headingPrefix(string $headingPrefix)
    {
        $this->headingPrefix = $headingPrefix;

        return $this;
    }

    /**
     * Set heading separator.
     * 
     * @param  string  $headingSeparator
     * @return  self
     */
    public function headingSeparator(string $headingSeparator)
    {
        $this->headingSeparator = $headingSeparator;

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

    /**
     * Make the heading for a given index.
     * 
     * @param  int|string  $index
     * @return  string
     */
    public function  makeHeadingForIndex($index)
    {
        $heading = $this->heading ?? $this->makeHeadingPrefixForIndex($index)  . ' ' . $this->getSingularLabel();

        return is_int($index) ? str_replace(self::INDEX, $index + 1, $heading) : $heading;
    }

    /**
     * Make the heading prefix for a given index.
     * 
     * @param  int|string  $index
     * @return  string
     */
    public function  makeHeadingPrefixForIndex($index)
    {
        $headingPrefix = $this->headingPrefix . $this->headingSeparator;

        return is_int($index) ? str_replace(self::INDEX, $index + 1, $headingPrefix) : $headingPrefix;
    }

    /**
     * Prepend to heading prefix.
     * 
     * @param  string  $prefix
     * @return void
     */
    public function preprendToHeadingPrefix(string $prefix)
    {
        $this->headingPrefix = $prefix . $this->headingPrefix;
    }


    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'INDEX' => self::INDEX,
            'ID' => self::ID,
            'SEPARATOR' => self::SEPARATOR,
            'pluralLabel' => $this->getPluralLabel(),
            'singularLabel' => $this->getSingularLabel(),
            'isManyRelationship' => $this->isManyRelationship(),
            'children' => $this->getChildren(),
            'schema' => $this->getSchema()
        ]);
    }

    /**
     * Checks chether the current route
     * is trying to find a subfield on this nested form.
     * 
     * @return  bool
     */
    protected static function isLookingForSubField()
    {
        return Str::contains(Route::currentRouteAction(), ['AssociatableController']) && !request('nestedFormResource');
    }


    /**
     * Returns the subfield the current route
     * is looking for.
     * 
     * @return  Field
     */
    protected static function findField(NestedForm $instance)
    {
        $request = request();

        $request->merge(['nestedFormResource' => $instance->resourceName]);

        $field = $instance->getSchema()->getFields()->findFieldInSchema($request->field);

        $field->attribute = $request->field;

        return $field;
    }

    /**
     * Create a new instance.
     * 
     * @return  NestedForm|Field
     */
    public static function make(...$args)
    {
        $instance = new static(...$args);

        if (static::isLookingForSubField()) {
            return static::findField($instance);
        }

        return $instance;
    }
}
