<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Collection;

class NestedFormChildren extends Collection
{

    /**
     * Create a new collection.
     *
     * @param  mixed  $items
     * @return void
     */
    public function __construct(Collection $items, NestedForm $parent)
    {
        $items = $items->map(function ($item, $index) use ($parent) {
            return new NestedFormChild($item, $index, $parent);
        })->all();

        parent::__construct($items);
    }

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
