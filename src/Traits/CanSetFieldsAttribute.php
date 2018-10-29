<?php

namespace Yassi\NestedForm\Traits;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;

trait CanSetFieldsAttribute
{

    /**
     * This method modifies the subfield attributes to nest them.
     *
     * @param FieldCollection $fields
     * @param int|string $index
     * @return Collection
     */
    protected function setFieldsAttribute(FieldCollection $fields)
    {
        $fields->each(function ($field) {
            $this->setOriginalAttributeForField($field);
            $field->attribute = $this->getNewFieldAttribute($field);
        });

        return $fields;
    }

    /**
     * Set the original attribute on a field.
     *
     * @param Field $field
     * @return Field
     */
    protected function setOriginalAttributeForField(Field $field)
    {
        if (!isset($field->meta['original_attribute'])) {
            $field->withMeta([
                'original_attribute' => $field->attribute,
            ]);
        }

        return $field;
    }

    /**
     * Get field attribute.
     *
     * @param Field $field
     * @param int|string $index
     * @return string
     */
    protected function getNewFieldAttribute(Field $field)
    {
        return self::ATTRIBUTE_PREFIX . $this->attribute . '[' . $field->meta['original_attribute'] . ']';
    }
}
