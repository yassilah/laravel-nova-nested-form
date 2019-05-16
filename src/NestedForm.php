<?php

namespace Yassi\NestedForm;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Yassi\NestedForm\Traits\CanBeOpened;
use Yassi\NestedForm\Traits\HasHeading;
use Yassi\NestedForm\Traits\CanFindSubfield;

class NestedForm extends Field
{
    use CanBeOpened, HasHeading, CanFindSubfield;

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
     * The name of the Eloquent relationship.
     *
     * @var string
     */
    public $viaRelationship;

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
    }

    /**
     * Get the list of children.
     * 
     * @return  Collection
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
     * @return  NestedFormChild
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
        return (new \ReflectionClass($resource->{$this->viaRelationship}()))->getShortName();
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
}
