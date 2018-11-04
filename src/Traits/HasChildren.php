<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

trait HasChildren
{

    /**
     * Add children.
     *
     * @return self
     */
    protected function setChildren()
    {
        return $this->withMeta([
            'children' => $this->getRelation()->get()->map(function ($model, $index) {
                return $this->setChild($model, $index);
            }),
        ]);
    }

    /**
     * Set child.
     *
     * @return self
     */
    protected function setChild(Model $model, $index = self::INDEX)
    {
        $this->setPrefix($index + 1)->setAttribute($index);

        $array = [
            'resourceId' => $model->id,
            'resourceName' => Nova::resourceForModel($this->getRelation()->getRelated())::uriKey(),
            'viaResource' => $this->viaResource,
            'viaRelationship' => $this->viaRelationship,
            'viaResourceId' => $this->viaResourceId,
            'heading' => $this->getHeading(),
            'attribute' => self::ATTRIBUTE_PREFIX . $this->attribute,
            'opened' => isset($this->meta['opened']) && ($this->meta['opened'] === 'only first' ? $index === 0 : $this->meta['opened']),
            'fields' => $this->setFieldsAttribute($this->updateFields($model))->values(),
            'max' => $this->meta['max'] ?? 0,
            'min' => $this->meta['min'] ?? 0,
            self::STATUS => null,
        ];

        $this->removePrefix()->removeAttribute();

        return $array;
    }

    /**
     * Get fields.
     *
     * @param Model $model
     * @param string|null $type
     *
     * @return FieldCollection
     */
    private function updateFields(Model $model)
    {
        return (new $this->relatedResource($model))->updateFields(NovaRequest::create('/'));
    }
}
