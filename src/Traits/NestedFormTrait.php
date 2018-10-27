<?php

namespace Yassi\NovaNestedForm\Traits;

use Laravel\Nova\Http\Requests\NovaRequest;

trait NestedFormTrait
{
    /**
     * Get the fields that are available for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Support\Collection
     */
    public function availableFields(NovaRequest $request)
    {
        if (str_contains($request->field, 'nested:')) {
            $request->field = preg_replace('/.*?(?:\[.*?\])*\[(.*?)\]$/', '$1', $request->field);
        }

        return parent::availableFields($request);
    }
}
