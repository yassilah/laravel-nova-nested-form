<?php


namespace Yassi\NovaNestedForm;

use Illuminate\Database\Eloquent\Model;
use Exception;
use ReflectionClass;
use Illuminate\Support\Str;

class Relationship
{

    /**
     * Relationship class.
     * 
     * @var string 
     */
    protected $class;

    /**
     * Relationship name.
     * 
     * @var string 
     */
    protected $name;

    /**
     * Relationship type.
     * 
     * @var string 
     */
    protected $type;

    /**
     * Relationship instance.
     * 
     * @var Relation 
     */
    protected $instance;

    /**
     * This method creates a new Relationship.
     * 
     * @return Relationsip
     */
    public static function make(...$args)
    {
        return new static(...$args);
    }

    /**
     * This method sets the name of the relationship class.
     * 
     * @param string $resourceClass
     * @return NovaNestedForm
     */
    protected function setClass(string $resourceClass)
    {
        $this->class = $resourceClass;

        return $this;
    }

    /**
     * This method sets the name of the relationship.
     * 
     * @return NovaNestedForm
     */
    protected function setName(string $name = null)
    {
        $this->name = $name ?? Str::snake($this->class::newModel()->getTable());

        return $this;
    }

    /**
     * This method sets the relationship instance.
     * 
     * @param Model $resoure
     * @return NovaNestedForm
     */
    protected function setInstance(Model $resource)
    {
        $this->checkRelationshipExistence($resource);

        $this->instance = $resource->{$this->name}();

        return $this;
    }

    /**
     * This method sets the  type.
     * 
     * @return NovaNestedForm
     */
    protected function setType()
    {
        $this->type = (new ReflectionClass($this->instance))->getShortName();

        return $this;
    }

    /**
     * This method verifies that the relationship exists on the 
     * current resource model.
     * 
     * @return bool 
     * @throws Exception
     */
    protected function checkRelationshipExistence(Model $resource)
    {
        if (method_exists($resource, $this->name)) {
            return true;
        } else if (method_exists($resource, str_singular($this->name))) {
            $this->setName(str_singular($this->name));
            return true;
        }

        throw new Exception('The relationship "' . $this->name . '" does not exist on model ' . get_class($resource) . '.');
    }


    /**
     * Get relationship class.
     *
     * @return  string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get relationship name.
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get relationship type.
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get relationship instance.
     *
     * @return  Relation
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Constructor method.
     * 
     * @param string $resourceClass
     * @param Model $resource
     */
    public function __construct(string $resourceClass, Model $resource)
    {
        $this->setClass($resourceClass)
            ->setName()
            ->setInstance($resource)
            ->setType();
    }
}