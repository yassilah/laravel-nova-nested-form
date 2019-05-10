<?php
namespace Yassi\NestedForm\Tests\RelationTest;


class HasManyTest extends RelationTestCase
{

    /**
     * Parent model class.
     *
     * @var Model
     */
    protected $modelClass = 'Yassi\NestedForm\Tests\Models\User';

    /**
     * Relationship to test.
     *
     * @var string
     */
    protected $relationshipName = 'posts';

    /**
     * Related resource to test.
     *
     * @var string
     */
    protected $relatedResource = 'Yassi\NestedForm\Tests\Resources\Post';
}
