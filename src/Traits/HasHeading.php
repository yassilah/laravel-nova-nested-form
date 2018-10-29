<?php

namespace Yassi\NestedForm\Traits;

trait HasHeading
{
    /**
     * Heading.
     *
     * @var string
     */
    protected $heading;

    /**
     * Separator.
     *
     * @var string
     */
    protected $separator = '.';

    /**
     * Set heading template.
     *
     * @param string $heading
     *
     * @return self
     */
    public function heading(string $heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * Set separator.
     *
     * @param string $separator
     *
     * @return self
     */
    public function separator(string $separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Default heading.
     *
     * @return string
     */
    protected function defaultHeading()
    {
        return str_singular($this->name);
    }

    /**
     * Set heading.
     *
     * @return self
     */
    protected function getHeading()
    {
        return trim($this->prefix . ' ' . ($this->heading ?? $this->defaultHeading()));
    }

}
