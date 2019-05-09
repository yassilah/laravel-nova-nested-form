<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Collection;

class NestedFormChildren extends Collection
{
    /**
     * Find field by attribute name.
     *
     * @param  string  $attribute
     * @return  Field
     */
    public function findField(string $attribute)
    {

        return $this->flatMap(function ($child) {
            return $child->fields;
        })->firstWhere('attribute', $attribute);
    }
}
