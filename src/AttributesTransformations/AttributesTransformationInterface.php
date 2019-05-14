<?php

namespace Yassi\NestedForm\AttributesTransformations;

use Laravel\Nova\Fields\Field;
use Yassi\NestedForm\NestedFormChild;

interface AttributesTransformationInterface
{

    /**
     * Transform the attributes of the given field
     * to make it compatible with this package.
     */
    static function transformAttributesOf(Field &$field, NestedFormChild $child);
}
