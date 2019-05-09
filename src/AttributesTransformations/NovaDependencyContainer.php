<?php
use Laravel\Nova\Fields\FieldCollection;

namespace Yassi\NestedForm\AttributesTransformations;

class NovaDependencyContainer implements AttributesTransformation
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
