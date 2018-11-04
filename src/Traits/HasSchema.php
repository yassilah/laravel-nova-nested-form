<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

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
                'resourceName' => Nova::resourceForModel($this->getRelation()->getRelated())::uriKey(),
                'viaRelationship' => $this->viaRelationship,
                'viaResourceId' => $this->viaResourceId,
                'heading' => $this->getHeading(),
                'attribute' => self::ATTRIBUTE_PREFIX . $this->attribute,
                'opened' => true,
                'max' => $this->meta['max'] ?? 0,
                'min' => $this->meta['min'] ?? 0,
                'fields' => $this->setFieldsAttribute($this->creationFields($this->getRelation()->getRelated()))->values(),
                self::STATUS => self::CREATED,
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
