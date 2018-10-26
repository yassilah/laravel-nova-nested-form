<?php

namespace Yassi\NovaNestedForm;

use Laravel\Nova\Fields\Field;
use Yassi\NovaNestedForm\Traits\HasRelationships;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Fields\FieldCollection;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;
use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Nova;
use Illuminate\Database\Eloquent\Model;
use Yassi\NovaNestedForm\Traits\RedirectsRequests;

class NovaNestedForm extends Field
{
    use HasRelationships, RedirectsRequests;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nested-form';

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * Indicates if the element should be shown on the detail view.
     *
     * @var bool
     */
    public $showOnDetail = false;


}
