<?php

namespace Yassi\NestedForm;

use Laravel\Nova\Fields\Field;
use Yassi\NestedForm\Traits\CanSetFieldsAttribute;
use Yassi\NestedForm\Traits\HasAttribute;
use Yassi\NestedForm\Traits\HasChildren;
use Yassi\NestedForm\Traits\HasHeading;
use Yassi\NestedForm\Traits\HasPrefix;
use Yassi\NestedForm\Traits\HasRelation;
use Yassi\NestedForm\Traits\HasResource;
use Yassi\NestedForm\Traits\HasSchema;

class NestedForm extends Field
{
    use HasPrefix, HasAttribute, HasHeading, HasRelation, HasResource, HasChildren, HasSchema, CanSetFieldsAttribute;

    /**
     * Constants for field status.
     */
    const UNCHANGED = 'unchanged';
    const CREATED = 'created';
    const UPDATED = 'updated';
    const REMOVED = 'removed';

    /**
     * Constants for placeholders.
     */
    const ATTRIBUTE_PREFIX = 'nested:';
    const INDEX = '{{index}}';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nested-form';

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
     * Constructor.
     */
    public function __construct(string $resourceClass, string $viaRelationship = null)
    {
        $this->setRelatedResource($resourceClass)->setViaRelationship($viaRelationship);
    }

    public function resolve($resource, $attribute = null)
    {
        $this->setResource($resource)
            ->setViaResource()
            ->setViaResourceId()
            ->setRelationType()
            ->setName()
            ->setChildren()
            ->setSchema();
    }

    /**
     * Set name.
     *
     * @return  self
     */
    protected function setName()
    {
        $this->name = title_case(str_singular($this->viaRelationship));

        return $this;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'component' => $this->component(),
            'INDEX' => self::INDEX,
            'ATTRIBUTE_PREFIX' => self::ATTRIBUTE_PREFIX,
        ], $this->meta());
    }
}
