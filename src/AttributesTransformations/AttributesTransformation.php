<?php
use Laravel\Nova\Fields\Field;
use Yassi\NestedForm\NestedFormChild;

namespace Yassi\NestedForm\AttributesTransformations;

interface AttributesTransformation
{

    /**
     * Transform the attributes of the given field
     * to make it compatible with this package.
     */
    static function transformAttributesOf(Field &$field, NestedFormChild $child);
}
