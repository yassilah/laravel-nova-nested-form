<?php

namespace Yassi\NestedForm\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use ReflectionClass;

trait HasRelation
{

    /**
     * Related resource.
     *
     * @var string
     */
    protected $relatedResource;

    /**
     * Relation name.
     *
     * @var string
     */
    protected $viaRelationship;

    /**
     * Relation resource.
     *
     * @var string
     */
    protected $viaResource;

    /**
     * Relation id.
     *
     * @var string
     */
    protected $viaResourceId;

    /**
     * Set relation.
     *
     * @return  self
     */
    protected function getRelation()
    {
        if (!method_exists($this->resource, $this->viaRelationship) && !method_exists($this->resource, $this->viaRelationship = str_singular($this->viaRelationship))) {
            throw new Exception('The relation "' . $this->viaRelationship . '" does not exist on this->resource ' . get_class($this->resource) . '.');
        }

        return $this->resource->{$this->viaRelationship}();
    }

    /**
     * Set relation type.
     *
     * @return  self
     */
    public function setRelationType()
    {
        return $this->withMeta(([
            snake_case((new ReflectionClass($this->getRelation()))->getShortName()) => true,
        ]));
    }

    /**
     * Set relation name.
     *
     * @param  string  $viaRelationship
     *
     * @return  self
     */
    public function setViaRelationship(string $viaRelationship = null)
    {
        $this->viaRelationship = $viaRelationship ?? $this->relatedResource::newModel()->getTable();

        return $this;
    }

    /**
     * Set related resource.
     *
     * @return  self
     */
    protected function setRelatedResource(string $resourceClass = null)
    {
        $this->relatedResource = $resourceClass ?? ResourceRelationshipGuesser::guessResource($this->name);

        return $this;
    }

    /**
     * Set relation resource.
     *
     * @param  string  $viaResource
     *
     * @return  self
     */
    public function setViaResource()
    {
        $this->viaResource = $this->resource->getTable();

        return $this;
    }

    /**
     * Set relation id.
     *
     * @param  string  $viaResourceId  Relation id.
     *
     * @return  self
     */
    public function setViaResourceId()
    {
        $this->viaResourceId = $this->resource->id;

        return $this;
    }
}
