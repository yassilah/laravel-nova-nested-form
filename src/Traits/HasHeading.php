<?php

namespace Yassi\NestedForm\Traits;

use Yassi\NestedForm\NestedForm;

trait HasHeading
{


    /**
     * Heading.
     *
     * @var string
     */
    protected $heading;

    /**
     * Heading separator.
     *
     * @var string
     */
    protected $headingSeparator = '.';

    /**
     * Current heading prefix.
     * 
     * @var string
     */
    protected $headingPrefix = NestedForm::INDEX;

    /**
     * Set heading.
     *
     * @param  string  $heading
     * @return  self
     */
    public function heading(string $heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * Set heading prefix.
     * 
     * @param  string  $headingPrefix
     * @return  self
     */
    public function headingPrefix(string $headingPrefix)
    {
        $this->headingPrefix = $headingPrefix;

        return $this;
    }

    /**
     * Set heading separator.
     * 
     * @param  string  $headingSeparator
     * @return  self
     */
    public function headingSeparator(string $headingSeparator)
    {
        $this->headingSeparator = $headingSeparator;

        return $this;
    }

    /**
     * Make the heading for a given index.
     * 
     * @param  int|string  $index
     * @return  string
     */
    public function  makeHeadingForIndex($index)
    {
        $heading = $this->heading ?? $this->makeHeadingPrefixForIndex($index)  . ' ' . $this->getSingularLabel();

        return is_int($index) ? str_replace(NestedForm::INDEX, $index + 1, $heading) : $heading;
    }

    /**
     * Make the heading prefix for a given index.
     * 
     * @param  int|string  $index
     * @return  string
     */
    public function  makeHeadingPrefixForIndex($index)
    {
        $headingPrefix = $this->headingPrefix . $this->headingSeparator;

        return is_int($index) ? str_replace(NestedForm::INDEX, $index + 1, $headingPrefix) : $headingPrefix;
    }

    /**
     * Prepend to heading prefix.
     * 
     * @param  string  $prefix
     * @return void
     */
    public function preprendToHeadingPrefix(string $prefix)
    {
        $this->headingPrefix = $prefix . $this->headingPrefix;
    }
}
