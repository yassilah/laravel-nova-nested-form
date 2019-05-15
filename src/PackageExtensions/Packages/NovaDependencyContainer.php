<?php

namespace Yassi\NestedForm\PackageExtensions\Packages;

use Laravel\Nova\Fields\FieldCollection;
use Yassi\NestedForm\PackageExtensions\PackageExtensionsInterface;
use Laravel\Nova\Fields\Field;
use Yassi\NestedForm\NestedFormChild;

class NovaDependencyContainer implements PackageExtensionsInterface
{

    /**
     * Transform the attributes of the given field
     * to make it compatible with this package.
     */
    static function transformAttributesOf(Field &$field, NestedFormChild $child): void
    {
        $child->recursivelyTransformAttributes(static::getFields($field));

        foreach ($field->meta['dependencies'] as &$dependency) {
            $dependency['field'] = $child->getTransformedAttribute($dependency['field']);
        }
    }

    /**
     * Get the list of fields contained by
     * this field.
     * 
     * @return  FieldCollection
     */
    static function getFields(Field $field): FieldCollection
    {
        return new FieldCollection($field->meta['fields']);
    }
}
