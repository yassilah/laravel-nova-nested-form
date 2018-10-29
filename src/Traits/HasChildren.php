<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;

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
            'viaResource' => $this->viaResource,
            'viaRelationship' => $this->viaRelationship,
            'viaResourceId' => $this->viaResourceId,
            self::STATUS => null,
            'heading' => $this->getHeading(),
            'attribute' => self::ATTRIBUTE_PREFIX . $this->attribute,
            'fields' => $this->setFieldsAttribute($this->updateFields($model))->values(),
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
