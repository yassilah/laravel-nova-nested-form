<?php

namespace Yassi\NestedForm\Requests;

use Laravel\Nova\Http\Requests\CreateResourceRequest;

class CustomCreateResourceRequest extends CreateResourceRequest
{
    protected $customResource = null;

    public function setCustomResource($customResourceClass)
    {
        $this->customResource = $customResourceClass;
    }

    public function resource()
    {
        return $this->customResource;
    }
}
