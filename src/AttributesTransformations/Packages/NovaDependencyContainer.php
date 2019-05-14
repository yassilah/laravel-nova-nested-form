<?php

namespace Yassi\NestedForm\AttributesTransformations\Packages;

use Laravel\Nova\Fields\FieldCollection;
use Yassi\NestedForm\AttributesTransformations\AttributesTransformationInterface;
use Laravel\Nova\Fields\Field;
use Yassi\NestedForm\NestedFormChild;

class NovaDependencyContainer implements AttributesTransformationInterface
{

    /**
     * Transform the attributes of the given field
     * to make it compatible with this package.
     */
    static function transformAttributesOf(Field &$field, NestedFormChild $child)
    {
        $child->recursivelyTransformAttributes(new FieldCollection($field->meta['fields']));

        foreach ($field->meta['dependencies'] as &$dependency) {
            $dependency['field'] = $child->getTransformedAttribute($dependency['field']);
        }
    }
}
