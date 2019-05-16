<?php

namespace Yassi\NestedForm\Traits;

use Yassi\NestedForm\NestedForm;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Laravel\Nova\Fields\Field;

trait CanFindSubfield
{

    /**
     * Checks chether the current route
     * is trying to find a subfield on this nested form.
     * 
     * @return  bool
     */
    protected static function isLookingForSubField()
    {
        return Str::contains(Route::currentRouteAction(), ['AssociatableController']) && !request('isLookingForSubfield');
    }


    /**
     * Returns the subfield the current route
     * is looking for.
     * 
     * @return  Field
     */
    protected static function findField(NestedForm $instance)
    {
        $request = request();

        $request->merge(['isLookingForSubfield' => true]);

        $field = $instance->getSchema()->getFields()->findFieldInSchema($request->field);

        $field->attribute = $request->field;

        return static::addFakeTraitMethods($field);
    }

    /**
     * Adds "fake" traits methods to prevent errors
     * when adding helper methods after the static "make" function
     * while returning a Field instead of a NestedForm class.
     * 
     * Example: NestedForm::make('Posts')->heading('Something') would
     * throw an error when going to route AssociatableController.
     */
    protected static function addFakeTraitMethods(Field $field)
    {
        $methods = collect((new ReflectionClass(NestedForm::class))->getTraits())->flatMap->getMethods();

        $fakeMethod = function () use ($field) {
            return $field;
        };

        foreach ($methods as $method) {
            $field::macro($method->name, $fakeMethod);
        }

        return $field;
    }

    /**
     * Create a new element.
     *
     * @return static
     */
    public static function make(...$arguments)
    {
        $instance = new static(...$arguments);

        if (static::isLookingForSubField()) {
            return static::findField($instance);
        }

        return $instance;
    }
}
