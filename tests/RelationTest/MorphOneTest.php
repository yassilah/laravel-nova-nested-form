<?php
namespace Yassi\NestedForm\Tests\RelationTest;

use Yassi\NestedForm\Tests\RelationTest\RelationTestCase;

class MorphOneTest extends RelationTestCase
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
    protected $relationshipName = 'comment';

    /**
     * Related resource to test.
     *
     * @var string
     */
    protected $relatedResource = 'Yassi\NestedForm\Tests\Resources\Comment';
}
