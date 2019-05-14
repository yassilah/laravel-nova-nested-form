<?php

namespace Yassi\NestedForm;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;

class NestedFormSchema implements JsonSerializable
{

    /**
     * Create a new schema.
     *
     * @param  Resource  $resource
     */
    public function __construct(Resource $resource)
    {
        $request = app(NovaRequest::class);

        $this->fields = $resource->availableFields($request)->filter(function ($field) use ($request) {
            if ($field instanceof BelongsTo && $field->resourceName === $request->resource) {
                return false;
            }

            return true;
        });
    }

    /**
     * Transform layout for serialization
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'fields' => $this->fields->toArray()
        ];
    }
}
