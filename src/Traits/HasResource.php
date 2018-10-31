<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasResource
{

    /**
     * Current resource.
     *
     * @var Model
     */
    protected $resource;

    /**
     * Set resource.
     *
     * @return  self
     */
    protected function setResource(Model $resource = null)
    {
        $this->resource = $resource ?? NovaRequest::createFrom(Request::instance())->resource()::newModel();

        return $this;
    }
}
