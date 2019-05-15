<?php
namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Collection;
use Yassi\NestedForm\PackageExtensions\HasPackageExtensions;

class NestedFormFields extends Collection
{

    use HasPackageExtensions;

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
     * Find field by attribute name in a schema.
     *
     * @param  string  $attribute
     * @return  Field
     */
    public function findFieldInSchema(string $attribute)
    {
        return $this->allFieldsInSchema()->firstWhere('attribute', preg_replace('/' . NestedForm::SEPARATOR . '[0-9]+' . NestedForm::SEPARATOR . '/', NestedForm::SEPARATOR . NestedForm::INDEX . NestedForm::SEPARATOR, $attribute));
    }

    /**
     * Get all the fields and subfields.
     * 
     * @return NestedFormChildren
     */
    public function allFields()
    {
        return $this->map(function ($field) {
            return $this->getFieldsUsingPackageExtensions($field);
        })->flatten()->map(function ($field) {
            return $field instanceof NestedForm ? $field->getChildren()->getFields()->allFields() : $field;
        })->flatten();
    }

    /**
     * Get all the fields and subfields in a schema.
     * 
     * @return NestedFormChildren
     */
    public function allFieldsInSchema()
    {
        return $this->map(function ($field) {
            return $this->getFieldsUsingPackageExtensions($field);
        })->flatten()->map(function ($field) {
            return $field instanceof NestedForm ? $field->getSchema()->getFields()->allFieldsInSchema() : $field;
        })->flatten();
    }
}
