<?php
namespace Yassi\NestedForm\Tests\RelationTest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Nova\Nova;
use Yassi\NestedForm\NestedForm;
use Yassi\NestedForm\Tests\TestCase;

class RelationTestCase extends TestCase
{

    /**
     * Parent model class.
     *
     * @var Model
     */
    protected $modelClass;

    /**
     * Relationship to test.
     *
     * @var string
     */
    protected $relationshipName;

    /**
     * Related resource to test.
     *
     * @var string
     */
    protected $relatedResource;

    /**
     * Model to test.
     *
     * @var Model
     */
    protected $model;

    /**
     * NestedForm instance.
     *
     * @var NestedForm
     */
    protected $nestedForm;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->model = $this->modelClass::inRandomOrder()->first();

        $this->nestedForm = NestedForm::make(Str::title($this->relationshipName), $this->relationshipName, $this->relatedResource);

        $this->nestedForm->resolve($this->model);
    }

    /** @test */
    public function it_can_fetch_the_name_of_the_related_resource()
    {
        $this->assertEquals($this->nestedForm->getRelateResourceName(), Nova::resourceForModel($this->modelClass)::uriKey());
    }

    /** @test */
    public function it_can_fetch_the_id_of_the_related_resource()
    {
        $this->assertEquals($this->nestedForm->getRelateResourceId(), $this->model->id);
    }

    /** @test */
    public function it_can_know_whether_relationship_is_many()
    {
        $this->assertEquals($this->nestedForm->isManyRelationship(), $this->isManyRelationship());
    }

    /** @test */
    public function it_can_properly_format_the_heading()
    {
        $title = Str::title(Str::singular($this->relatedResource::uriKey()));

        $this->assertEquals($this->nestedForm->getFormattedHeading(), NestedForm::INDEX . '.' . ' ' . $title);

        $this->nestedForm->headingSeparator('-');
        $this->assertEquals($this->nestedForm->getFormattedHeading(), NestedForm::INDEX . '-' . ' ' . $title);

        $this->nestedForm->headingPrefix('MyPrefix');
        $this->assertEquals($this->nestedForm->getFormattedHeading(), 'MyPrefix' . '-' . ' ' . $title);

        $this->nestedForm->heading(NestedForm::INDEX . ' - Something');
        $this->assertEquals($this->nestedForm->getFormattedHeading(), NestedForm::INDEX . ' - Something');
    }

    /** @test */
    public function it_can_set_the_right_amount_of_children_resources()
    {
        $this->assertCount($this->model->{$this->relationshipName}()->count(), $this->nestedForm->getChildren());
    }





    /**
     * Get the type of relationship to test.
     *
     * @return string
     */
    protected function getRelationshipTestType()
    {
        return Str::replaceLast('Test', '', (new \ReflectionClass($this))->getShortName());
    }

    /**
     * Check whether the current relationship is
     * a Many or One relationship.
     *
     * @return bool
     */
    public function isManyRelationship()
    {
        return Str::contains($this->getRelationshipTestType(), 'Many');
    }
}
