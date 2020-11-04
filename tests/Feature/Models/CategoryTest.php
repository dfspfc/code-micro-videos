<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testListAllCategoryAttributes()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKey = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            $categoryKey
        );
    }

    public function testCreatePassingOnlyNameShouldSetTheRestOfTheAtrributesToTheirDefault()
    {
        $expectedName = 'test';
        $category = Category::create(['name' => $expectedName]);
        $category->refresh();

        $this->assertEquals($expectedName, $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        $this->assertTrue(Category::isValid($category->id));
    }

    public function testCreateWithAttributeIsActiveFalseShouldSetItToFalse()
    {
        $category = Category::create(['name' => 'test', 'is_active' => false]);
        $category->refresh();

        $this->assertFalse($category->is_active);
    }

    public function testCreatePassingAttributeDescriptionShouldSetItAccording()
    {
        $expectedDescription = 'desc test';
        $category = Category::create(['name' => 'test', 'description' => $expectedDescription]);

        $this->assertEquals($expectedDescription, $category->description);
    }

    public function testUpdate()
    {
        $category = Category::create(['name' => 'test']);

        $updateData = [
            'name' => 'test updated name',
            'description' => 'test updated description',
            'is_active' => false,
        ];
        $category->update($updateData);

        foreach($updateData as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDeleteShouldSoftDeleteTheCategory()
    {
        $category = Category::create(['name' => 'test']);
        $createdCategoryId = $category->id;
        $category->delete();

        $deletedCategory = Category::where('id', $createdCategoryId)
            ->withTrashed()
            ->get()
            ->first();
        
        $this->assertNotNull($deletedCategory->deleted_at);
    }
}
