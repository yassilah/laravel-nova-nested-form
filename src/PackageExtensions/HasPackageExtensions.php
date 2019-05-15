<?php


namespace Yassi\NestedForm\PackageExtensions;

use Laravel\Nova\Fields\FieldCollection;
use Illuminate\Support\Str;
use ReflectionClass;
use Yassi\NestedForm\NestedForm;
use Laravel\Nova\Fields\Field;

trait HasPackageExtensions
{

    /**
     * Package extensions classes.
     * 
     * @var array
     */
    static $packageExtensions;

    /**
     * Store and require the available attributes 
     * transformations.
     * 
     * @return  array
     */
    public static function storePackageExtensions()
    {
        static::$packageExtensions = [];

        foreach (glob(__DIR__ . '/Packages/*.php') as $filename) {
            $class = __NAMESPACE__ . '\Packages\\' . Str::replaceLast('.php', '', basename($filename));
            $reflect = new ReflectionClass($class);

            if ($reflect->implementsInterface(PackageExtensionsInterface::class)) {
                static::$packageExtensions[] = $class;
            }
        }
    }

    /**
     * Verify if the current field has a specific attribute transformation.
     * 
     * @param  Field  $field
     * @return  void
     */
    protected function transformAttributesUsingPackageExtensions(Field $field)
    {
        foreach (static::$packageExtensions as $class) {
            if (static::getShortName($field) === static::getShortName($class)) {
                $class::transformAttributesOf($field, $this);
                break;
            }
        }
    }

    /**
     * Get the list of fields.
     * 
     * @param  Field  $field
     * @return  FieldCollection
     */
    protected function getFieldsUsingPackageExtensions(Field $field)
    {
        foreach (static::$packageExtensions as $class) {
            if (static::getShortName($field) === static::getShortName($class)) {
                return $class::getFields($field);
            }
        }

        return $field;
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
