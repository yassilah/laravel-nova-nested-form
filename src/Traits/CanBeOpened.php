<?php

namespace Yassi\NestedForm\Traits;

trait CanBeOpened
{
    /**
     * Opened.
     *
     * @var bool
     */
    public $opened = false;

    /**
     * Set opened.
     *
     * @param  bool|string  $opened
     * @return  self
     */
    public function open($opened)
    {
        $this->opened = $opened;

        return $this;
    }
}
