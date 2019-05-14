<?php


namespace Yassi\NestedForm\AttributesTransformations;

use Laravel\Nova\Fields\FieldCollection;
use Illuminate\Support\Str;
use ReflectionClass;
use Yassi\NestedForm\NestedForm;
use Laravel\Nova\Fields\Field;

trait CanTransformAttributes
{

    /**
     * Attributes transformation classes.
     * 
     * @var array
     */
    static $attributesTransformations;

    /**
     * Recursively transform attributes.
     *
     * @param  FieldCollection  $fields
     * @return  self
     */
    public function recursivelyTransformAttributes(FieldCollection $fields)
    {

        $fields->each(function ($field) {
            if ($field instanceof NestedForm) {
                $field->preprendToHeadingPrefix($this->parent->makeHeadingPrefixForIndex($this->index));
            }
            $field->originalAttribute = $field->attribute;
            $field->attribute = $this->getTransformedAttribute($field->attribute);
            $this->runAttributesTransformations($field);
        });

        return $fields;
    }

    /**
     * Get the transformed attribute.
     *
     * @param  string  $attribute
     * @return  string
     */
    public function getTransformedAttribute(string $attribute = null)
    {
        return $this->parent->attribute . NestedForm::SEPARATOR . $this->index . ($attribute ? NestedForm::SEPARATOR . $attribute : '');
    }

    /**
     * Store and require the available attributes 
     * transformations.
     * 
     * @return  array
     */
    public static function storeAttributesTransformations()
    {
        static::$attributesTransformations = [];

        foreach (glob(__DIR__ . '/Packages/*.php') as $filename) {
            $class = __NAMESPACE__ . '\Packages\\' . Str::replaceLast('.php', '', basename($filename));
            $reflect = new ReflectionClass($class);

            if ($reflect->implementsInterface(AttributesTransformationInterface::class)) {
                static::$attributesTransformations[] = $class;
            }
        }
    }

    /**
     * Verify if the current field has a specific transformation.
     * 
     * @param  Field  $field
     * @return  void
     */
    protected function runAttributesTransformations(Field $field)
    {
        foreach (static::$attributesTransformations as $class) {
            if (static::getShortName($field) === static::getShortName($class)) {
                $class::transformAttributesOf($field, $this);
                break;
            }
        }
    }


    /**
     * Get the shortname of the given class.
     * 
     * @param  mixed  $class
     * @return  string
     */
    protected static function getShortName($class)
    {
        return (new ReflectionClass($class))->getShortName();
    }
}
