<?php

namespace Yassi\NestedForm\Traits;

trait HasSchema
{

    /**
     * The related schema.
     *
     * @var array
     */
    protected $schema;

    /**
     * Set the related schema.
     *
     * @return self
     */
    public function setSchema()
    {
        return $this->withMeta([
            'schema' => [
                'fields' => $this->getFields('showOnCreation'),
                'heading' => $this->getHeading(),
                'opened' => $this->meta['opened'] ?? false,
                'attribute' => $this->attribute . '[' . self::INDEX . ']',
            ],
        ]);
    }
}
