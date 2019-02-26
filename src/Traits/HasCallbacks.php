<?php

trait HasCallbacks
{

    /**
     * Callback after the nested children were attached.
     *
     * @var callable
     */
    protected $afterFillCallback;

    /**
     * Callback before the nested children were attached.
     *
     * @var callable
     */
    protected $beforeFillCallback;

    /**
     * Register a global callback or a callback for
     * specific attributes (children) after it has been filled.
     *
     * @param callable $callback
     *
     * @return self
     */
    public function afterFill(callable $callback)
    {
        $this->afterFillCallback = $callback;

        return $this;
    }

    /**
     * Register a global callback or a callback for
     * specific attributes (children) before it has been filled.
     *
     * @param callable $callback
     *
     * @return self
     */
    public function beforeFill(callable $callback)
    {
        $this->beforeFillCallback = $callback;

        return $this;
    }
}
