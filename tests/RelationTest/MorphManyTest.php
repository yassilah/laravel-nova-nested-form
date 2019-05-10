<?php
namespace Yassi\NestedForm\Tests\RelationTest;

class MorphManyTest extends RelationTestCase
{

    /**
     * Parent model class.
     *
     * @var Model
     */
    protected $modelClass = 'Yassi\NestedForm\Tests\Models\Post';

    /**
     * Relationship to test.
     *
     * @var string
     */
    protected $relationshipName = 'comments';

    /**
     * Related resource to test.
     *
     * @var string
     */
    protected $relatedResource = 'Yassi\NestedForm\Tests\Resources\Comment';

}
