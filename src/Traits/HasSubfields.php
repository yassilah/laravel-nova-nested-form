<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Contracts\ListableField;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\ResourceToolElement;

trait HasSubfields
{
    /**
     * Inverse of the current relationship.
     *
     * @var string
     */
    public $inverseRelationship;

    /**
     * Inverse of the current relationship.
     *
     * @var string
     */
    public $inverseRelationshipKey;

    /**
     * Get the related fields.
     *
     * @param string $filterKey
     * @param Model|null $model
     * @param $index
     * @return self
     */
    public function getFields(string $filterKey, Model $model = null, $index = self::INDEX)
    {
        return $this->filteredFields($filterKey)->map(function ($field) use ($index, $model) {
            $field->withMeta([
                'original_attribute' => $field->attribute,
                'attribute' => ($this->meta['attribute'] ?? $this->attribute) . ($this->isManyRelationship() ? '[' . $index . ']' : '') . '[' . $field->attribute . ']',
            ]);

            if ($field->component === $this->component) {
                $field->prefix = ($this->prefix ? $this->prefix : '') . (is_int($index) ? $index + 1 : $index) . $field->separator;
            }

            $field->resolve($model ?? $this->resourceInstance::newModel());

            return $field;
        })->values();
    }

    /**
     * Filter the fields without resolving them yet
     * and set the inverse relationship if need be.
     *
     * @param string $filterKey
     * @return FieldCollection
     */
    protected function filteredFields(string $filterKey)
    {
        return $this->resourceInstance->availableFields($this->request)->reject(function ($field) use ($filterKey) {
            return $field instanceof ListableField ||
                $field instanceof ResourceToolElement ||
                $field->attribute === $this->resourceInstance::newModel()->getKeyName() ||
                $field->attribute === 'ComputedField' ||
                !$field->$filterKey || (isset($field->resourceName) && ($field->resourceName === $this->meta['viaResource']) && $this->setInverseRelationship($field->attribute)) || ($this->request->getMethod() === 'GET' && ($field instanceof MorphTo || $field instanceof MorphToMany));
        });
    }

    /**
     * Set inverse of the current relationship.
     *
     * @param string $inverseRelationship
     * @return self
     */
    public function setInverseRelationship(string $inverseRelationship)
    {
        $this->inverseRelationship = $inverseRelationship;

        $foreignKeyMethod = $this->isUsingNova2() ? 'getForeignKeyName' : 'getForeignKey';

        $this->inverseRelationshipKey = $this->resourceInstance::newModel()->{$inverseRelationship}()->{$foreignKeyMethod}();

        return $this;
    }
}
