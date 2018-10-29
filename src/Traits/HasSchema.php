<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasSchema
{

    /**
     * Add schema.
     *
     * @return self
     */
    protected function setSchema()
    {
        $this->setPrefix()->setAttribute();

        $this->withMeta([
            'schema' => [
                'viaResource' => $this->viaResource,
                'viaRelationship' => $this->viaRelationship,
                'viaResourceId' => $this->viaResourceId,
                self::STATUS => self::CREATED,
                'heading' => $this->getHeading(),
                'attribute' => self::ATTRIBUTE_PREFIX . $this->attribute,
                'fields' => $this->setFieldsAttribute($this->creationFields($this->getRelation()->getRelated()))->values(),
            ],
        ]);

        $this->removePrefix()->removeAttribute();

        return $this;

    }

    /**
     * Get fields.
     *
     * @param Model $model
     * @param string|null $type
     *
     * @return FieldCollection
     */
    private function creationFields(Model $model)
    {
        return (new $this->relatedResource($model))->creationFields(NovaRequest::create('/'));
    }
}
