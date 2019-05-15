<?php

namespace Yassi\NestedForm;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Yassi\NestedForm\PackageExtensions\HasPackageExtensions;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('nested-form', __DIR__ . '/../dist/js/field.js');
            Nova::style('nested-form', __DIR__ . '/../dist/css/field.css');
        });

        HasPackageExtensions::storePackageExtensions();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
