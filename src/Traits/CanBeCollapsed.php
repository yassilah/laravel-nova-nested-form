<?php

namespace Yassi\NestedForm\Traits;

trait CanBeCollapsed
{

    /**
     * Set whether the forms should be opened on display.
     *
     * @param bool|string
     *
     * @return self
     */
    public function open($opened = false)
    {
        return $this->withMeta([
            'opened' => $opened,
        ]);
    }
}
