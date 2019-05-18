<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasLimits
{

    /**
     * Get all applicable rules.
     * 
     * @return  array
     */
    protected function getAllApplicableRules()
    {
        $request = app(NovaRequest::class);

        return array_merge(
            $this->getRules($request)[$this->attribute],
            call_user_func([$this, $this->getIsInSchema() ? 'getCreationRules' : 'getUpdateRules'], $request)[$this->attribute]
        );
    }

    /**
     * Get minimum and maximum number of children.
     *
     * @return  self
     */
    public function getLimits()
    {
        $rules = $this->getAllApplicableRules();
        $limits = [];

        foreach ($rules as $rule) {
            if (Str::contains($rule, 'min')) {
                $limits['min'] = (int)explode(':', $rule)[1];
            } else if (Str::contains($rule, 'max')) {
                $limits['max'] = (int)explode(':', $rule)[1];
            }
        }

        return $limits;
    }
}
