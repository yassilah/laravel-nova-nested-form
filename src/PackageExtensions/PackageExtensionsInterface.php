<?php

namespace Yassi\NestedForm\PackageExtensions;

use Laravel\Nova\Fields\Field;
use Yassi\NestedForm\NestedFormChild;
use Laravel\Nova\Fields\FieldCollection;

interface PackageExtensionsInterface
{

    /**
     * Transform the attributes of the given field
     * to make it compatible with this package.
     */
    static function transformAttributesOf(Field &$field, NestedFormChild $child): void;

    /**
     * Get the list of fields contained by
     * this field.
     * 
     * @return  FieldCollection
     */
    static function getFields(Field $field): FieldCollection;
}
