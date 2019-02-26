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
    public function setUp()
    {
        parent::setUp();

        $this->model = $this->modelClass::inRandomOrder()->first();

        $this->nestedForm = new NestedForm(Str::title($this->relationshipName), $this->relationshipName, $this->relatedResource);

        $this->nestedForm->setRequest($this->model);
    }

    /**
     * Get the type of relationship to test.
     *
     * @return string
     */
    protected function getRelationshipTestType()
    {
        return str_replace('Test', '', (new \ReflectionClass($this))->getShortName());
    }

    /**
     * Check whether the current relationship is
     * a Many or One relationship.
     *
     * @return bool
     */
    public function isManyRelationship()
    {
        return str_contains($this->getRelationshipTestType(), 'Many');
    }

    /** @test */
    public function it_can_get_the_relationship_type()
    {
        $this->assertEquals($this->nestedForm->getRelationshipType(), $this->getRelationshipTestType());
    }

    /** @test */
    public function it_can_add_the_relationship_type_to_the_meta()
    {
        $this->nestedForm->addRelationshipType();

        $this->assertArrayHasKey(Str::snake($this->getRelationshipTestType()), $this->nestedForm->meta);

        $this->assertTrue($this->nestedForm->meta[Str::snake($this->getRelationshipTestType())]);
    }

    /** @test */
    public function it_can_add_whether_the_relationship_is_many_to_the_meta()
    {
        $this->nestedForm->addIsManyRelationship();

        $this->assertArrayHasKey('is_many', $this->nestedForm->meta);

        $this->assertEquals($this->nestedForm->meta['is_many'], $this->isManyRelationship());
    }

    /** @test */
    public function it_can_set_the_request()
    {
        $request = $this->nestedForm->getRequest();

        $this->assertEquals($request->resource, Nova::resourceForModel($this->model)::uriKey());
        $this->assertEquals($request->id, $this->model->id);
    }

    /** @test */
    public function it_can_add_the_children_to_the_meta()
    {
        $this->nestedForm->resolve($this->model);
        $this->assertArrayHasKey('children', $this->nestedForm->meta);
    }

    /** @test */
    public function it_has_the_right_amount_of_children()
    {
        $this->nestedForm->resolve($this->model);
        $this->assertCount(count($this->nestedForm->meta['children']), $this->model->{$this->relationshipName}()->get());
    }

    /** @test */
    public function it_has_set_the_nested_attributes_on_each_child()
    {
        $this->nestedForm->resolve($this->model);

        $this->verifyNestedAttributes($this->nestedForm->meta['children']->toArray(), $this->nestedForm);
    }

    /** @test */
    public function it_has_set_the_nested_headings_on_each_nested_form_child()
    {
        $this->nestedForm->resolve($this->model);

        $this->verifyNestedHeadings($this->nestedForm->meta['children']->toArray(), $this->nestedForm);
    }

    /**
     * Verify whether the nested attributes were set correctly
     *
     * @param array $children
     * @param NestedForm $form
     * @param NestedForm $form
     *
     * @return void
     */
    public function verifyNestedAttributes(array $children, NestedForm $form)
    {
        foreach ($children as $index => $child) {
            foreach ($child->fields as $field) {
                $shouldBe = $form->getNestedAttribute() . ($form->isManyRelationship() ? '[' . $index . ']' : '') . '[' . $field->attribute . ']';

                $this->assertEquals($field->meta['attribute'], $shouldBe);
                $this->assertEquals($field->meta['originalAttribute'], $field->attribute);

                if ($field instanceof NestedForm) {
                    $this->verifyNestedAttributes($field->meta['children']->toArray(), $field);
                }
            }
        }
    }

    /**
     * Verify whether the nested headings were set correctly
     *
     * @param array $children
     * @param NestedForm $form
     * @param NestedForm $form
     *
     * @return void
     */
    public function verifyNestedHeadings(array $children, NestedForm $form, &$headings)
    {

        foreach ($children as $index => $child) {
            foreach ($child->fields as $field) {
                if ($field instanceof NestedForm) {
                    $this->verifyNestedHeadings($field->meta['children']->toArray(), $field, $headings);
                }
            }
        }
    }

    /** @test */
    public function it_can_add_the_schema_to_the_meta()
    {
        $this->nestedForm->resolve($this->model);
        $this->assertArrayHasKey('schema', $this->nestedForm->meta);
    }
}
