<?php

namespace Yassi\NovaNestedForm\Traits;

use ReflectionClass;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\FieldCollection;
use Yassi\NovaNestedForm\Relationship;
use Laravel\Nova\Fields\Field;

trait HasRelationships
{

    /**
     * Relationship.
     * 
     * @var Relationship
     */
    protected $relationship;

    /**
     * Index.
     * 
     * @var int
     */
    protected $index;

    /**
     * This method sets the relationship of the current resource.
     * 
     * @param Model $resource
     * @return NovaNestedForm
     */
    protected function setRelationship(Model $resource)
    {
        $this->relationship = Relationship::make($this->name, $resource);

        return $this->withMeta([
            'relationship' => $this->relationship->getName(),
            'viaResource' => $this->relationship->getInstance()->getParent()->getTable()
        ]);
    }

    /**
     * This method resolves the relationship based on its type.
     * 
     * @return NovaNestedForm
     * @throws Exception
     */
    protected function resolveRelationship()
    {
        if (method_exists($this, 'resolve' . $this->relationship->getType() . 'Relationship')) {
            return $this->{'resolve' . $this->relationship->getType() . 'Relationship'}();
        }

        throw new Exception('This type of relation is not yet handled.');
    }

    /**
     * This method resolves a HasOne relationship and adds its fields
     * to the meta property.
     * 
     * @return NovaNestedForm
     */
    protected function resolveHasOneRelationship()
    {
        return $this->resolveHasManyRelationship();
    }

    /**
     * This method resolves a HasMany relationship and adds its fields
     * to the meta property.
     * 
     * @return NovaNestedForm
     */
    protected function resolveHasManyRelationship()
    {
        return $this->withMeta([
            'children' => $this->relationship->getInstance()->get()->map(function ($resource, $index) {
                return (object)[
                    'fields' => $this->getRelatedUpdateFields($resource)->values(),
                    'resourceId' => $resource->id,
                    'status' => 'unchanged'
                ];
            })->values()
        ]);
    }

    /**
     * This method adds the current related resource schema to the meta property. 
     * 
     * @return NovaNestedResource
     */
    protected function setSchema()
    {
        return $this->withMeta([
            'schema' => (object)[
                'fields' => $this->getRelatedCreationFields()->values(),
                'status' => 'created'
            ]
        ]);
    }

    /**
     * This method adds the current related resource type to the meta property. 
     * 
     * @return NovaNestedResource
     */
    protected function setType()
    {
        return $this->withMeta([
            snake_case($this->relationship->getType()) => true
        ]);
    }

    /**
     * This method retrieves the creation fields for the related resource.
     * 
     * @return FieldCollection
     */
    protected function getRelatedCreationFields()
    {
        return (new $this->name($this->relationship->getInstance()->getRelated()))
            ->creationFields(NovaRequest::createFrom(request()));
    }

    /**
     * This method retrieves the update fields for the related resource.
     * 
     * @param Model $resource
     * @return FieldCollection
     */
    protected function getRelatedUpdateFields(Model $resource)
    {
        return (new $this->name($resource))
            ->updateFields(NovaRequest::createFrom(request()));
    }

    /**
     * This method modifies the subfield attributes to nest them.
     * 
     * @param FieldCollection $fields
     * @param int|string $index
     * @return Collection
     */
    protected function setFieldsAttribute(FieldCollection $fields, $index)
    {
        $fields->each(function ($field) use ($index) {
            $this->setOriginalAttributeForField($field);

            $field->attribute = $this->attribute . '[' . $index . '][' . $field->meta['original_attribute'] . ']';

            if ($field->component === $this->component) {
                //$field->meta['heading'] = (is_int($index) ? $index + 1 : $index) . '.' . $field->meta['heading'];
                $field->setChildrenAttribute()->setSchemaAttribute();
            }
        });

        return $fields;
    }

    /**
     * Sets the original attribute on a field.
     * 
     * @param Field $field
     * @return Field
     */
    protected function setOriginalAttributeForField(Field $field)
    {
        if (!isset($field->meta['original_attribute'])) {
            $field->withMeta([
                'original_attribute' => $field->attribute
            ]);
        }

        return $field;
    }

    /**
     * This method modifies the children attributes to nest them.
     * 
     * @return Collection
     */
    protected function setChildrenAttribute()
    {
        $this->meta['children']->each(function ($child, $index) {
            $this->setFieldsAttribute($child->fields, $index);
        });

        return $this;
    }

    /**
     * This method modifies the schema attributes to nest them.
     * 
     * @return Collection
     */
    protected function setSchemaAttribute()
    {
        $this->setFieldsAttribute($this->meta['schema']->fields, '{{index}}');

        return $this;
    }

    /**
     * This method sets the attribute of the current field.
     * 
     * @return NovaNestedForm
     */
    protected function setAttribute()
    {
        $this->attribute = 'nested:' . $this->relationship->getName();

        return $this;
    }

    /**
     * This method sets the name of the current field.
     * 
     * @return NovaNestedForm
     */
    protected function setName()
    {
        $this->name = str_singular(title_case($this->attribute));

        return $this;
    }

    /**
     * This is the default heading.
     * 
     * @return string
     */
    protected function defaultHeading()
    {
        return $this->relationship->getClass()::singularLabel() . ' - ' . '{{' . $this->relationship->getClass()::$title . '}}';
        //return ($this->meta['has_many'] ? '{{index}}. ' : '') . $this->relationship->getClass()::singularLabel() . ' - ' . '{{' . $this->relationship->getClass()::$title . '}}';
    }

    /**
     * This indicates the heading of the nested form.
     * 
     * @return NovaNestedForm
     */
    public function heading(string $template = null, string $separator = '.')
    {
        return $this->withMeta([
            'heading' => $template
        ]);
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        $this->setRelationship($resource)
            ->setAttribute()
            ->resolveRelationship()
            ->setSchema()
            ->setName()
            ->setType()
            ->setChildrenAttribute()
            ->setSchemaAttribute();

        if (!isset($this->meta['heading'])) {
            $this->heading($this->defaultHeading());
        }
    }
}