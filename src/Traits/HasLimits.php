<?php

namespace Yassi\NestedForm\Traits;

trait HasLimits
{

    /**
     * Set a maximum number of children.
     *
     * @param int $max
     *
     * @return self
     */
    public function max(int $max)
    {
        return $this->withMeta([
            'max' => $max,
        ]);
    }

    /**
     * Set a minimum number of children.
     *
     * @param int $min
     *
     * @return self
     */
    public function min(int $min)
    {
        return $this->withMeta([
            'min' => $min,
        ]);
    }
}
