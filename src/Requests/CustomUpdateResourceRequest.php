<?php

namespace Yassi\NestedForm\Requests;

use Laravel\Nova\Http\Requests\UpdateResourceRequest;

class CustomUpdateResourceRequest extends UpdateResourceRequest
{
    protected $customResource = null;

    public function setCustomResource($customResourceClass, $customResourceId)
    {
        $customResource = $customResourceClass::newModel()->find($customResourceId);
        $this->customResource = new $customResourceClass($customResource);
    }

    public function resource()
    {
        return $this->customResource;
    }
}
