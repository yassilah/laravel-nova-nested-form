<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Support\Facades\Request;

trait HasPrefix
{

    /**
     * Heading prefix (whole).
     *
     * @var string
     */
    protected $index = '';

    /**
     * Current prefix.
     *
     * @var string
     */
    protected $currentPrefix = '';

    /**
     * Set prefix.
     *
     * @param  string  $index.
     *
     * @return  self
     */
    public function setPrefix($index = self::INDEX)
    {

        if (isset($this->meta['has_many'])) {
            $this->currentPrefix = $index . $this->separator;
        }

        $this->prefix = Request::get('nested-prefix') . $this->currentPrefix;
        Request::merge(['nested-prefix' => $this->prefix]);

        return $this;
    }

    /**
     * Remove the last part of the prefix that was added.
     *
     * @return self
     */
    protected function removePrefix()
    {
        Request::merge(['nested-prefix' => str_replace_last($this->currentPrefix, '', $this->prefix)]);

        return $this;
    }
}
