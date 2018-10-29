<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Support\Facades\Request;

trait HasAttribute
{

    /**
     * Current attribute prefix.
     *
     * @var string
     */
    protected $currentAttribute = '';

    /**
     * Set attribute.
     *
     * @param  string  $index.
     *
     * @return  self
     */
    public function setAttribute($index = self::INDEX)
    {
        $viaRelationship = Request::get('nested-attribute') ? '[' . $this->viaRelationship . ']' : $this->viaRelationship;
        $this->currentAttribute = isset($this->meta['has_many']) ? $viaRelationship . '[' . $index . ']' : $viaRelationship;
        $this->attribute = Request::get('nested-attribute') . $this->currentAttribute;
        Request::merge(['nested-attribute' => $this->attribute]);
        return $this;
    }

    /**
     * Remove the last part of the attribute that was added.
     *
     * @return self
     */
    protected function removeAttribute()
    {
        Request::merge(['nested-attribute' => str_replace_last($this->currentAttribute, '', $this->attribute)]);

        return $this;
    }
}
