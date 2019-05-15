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
        return $this->allFields()->firstWhere('attribute', $attribute);
    }

    /**
     * Get all the fields and subfields.
     * 
     * @return NestedFormChildren
     */
    public function allFields()
    {
        return $this->flatMap->getFields()->map(function ($field) {
            return $field instanceof NestedForm ? $field->getChildren()->allFields() : $field;
        })->flatten();
    }
}
