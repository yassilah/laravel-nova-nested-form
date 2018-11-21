<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasChildren
{

    /**
     * The related children.
     *
     * @var array
     */
    protected $children;

    /**
     * Set the related children.
     *
     * @return self
     */
    public function setChildren(Model $resource)
    {
        return $this->withMeta([
            'children' => $resource->{$this->viaRelationship}()->get()->map(function ($item, $index) {
                return [
                    'fields' => $this->getFields('showOnUpdate', $item, $index),
                    'heading' => $this->getHeading(),
                    'opened' => $this->meta['opened'] ?? false,
                    'attribute' => $this->attribute . '[' . $index . ']',
                    self::ID => $item->id,
                ];
            }),
        ]);
    }
}
