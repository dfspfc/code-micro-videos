<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CategoryTest extends TestCase
{
    use DatabaseMigrations, JsonFragmentValidation;

    public function testListShouldReturn200()
    {
        $category = factory(Category::class)->create()->toArray();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category]);
    }

    public function testShowSpecificCategoryShouldReturn200()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));
        
        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testCreatePassingNoAttributesShouldReturn422()
    {
        $response = $this->json('POST', route('categories.store'), []);
        $this->assertRequired($response, 'name');
    }

    public function testCreatePassingAttributesLargerThan255CharactersShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('categories.store'),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertMax255($response, 'name');
    }

    public function testCreatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('categories.store'),
            [
                'is_active' => 'a',
            ]
        );
        $this->assertBoolean($response, 'is_active');
    }

    public function testUpdateNotPassingAnyAttributeShouldReturn422()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id],
            ),
            []
        );
        $this->assertRequired($response, 'name');
    }

    public function testUpdatePassingAttributesLargeThan255CharactersShouldReturn422()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id],
            ),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertMax255($response, 'name');
    }

    public function testUpdatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id],
            ),
            [
                'is_active' => 'a',
            ]
        );
        $this->assertBoolean($response, 'is_active');
    }

    public function testCreatePassingAttributeNameShouldCreateWithAllDefaultFieldsAndReturn201()
    {
        $response = $this->json(
            'POST',
            route('categories.store'),
            ['name' => 'valid name']
        );
        $id = $response->json('id');
        $category = Category::find($id);
        
        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));
    }

    public function testCreatePassingAllFiledsShouldCreateAndReturn201()
    {
        $requestBody = [
            'name' => 'valid name',
            'is_active' => false,
            'description' => 'valid description',
        ];
        $response = $this->json(
            'POST',
            route('categories.store'),
            $requestBody
        );
        
        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => $requestBody['name'],
                'description' => $requestBody['description'],
                'is_active' => $requestBody['is_active'],
            ]);
    }

    public function testUpdateShouldUpdateAndReturn200()
    {
        $category = factory(Category::class)->create([
            'name' => 'name to be updated',
            'description' => 'description to be updated',
            'is_active' => false,
        ]);
        $updateRequestBody = [
            'name' => 'new name',
            'description' => 'new description',
            'is_active' => true,
        ];
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id],
            ),
            $updateRequestBody
        );
        
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => $updateRequestBody['name'],
                'description' => $updateRequestBody['description'],
                'is_active' => $updateRequestBody['is_active'],
            ]);
    }

    public function testDeleteShouldSoftDeleteCategoryAndReturn204()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'DELETE',
            route(
                'categories.destroy',
                ['category' => $category->id],
            ),
            []
        );
        $response->assertStatus(204);

        $deletedCategory = Category::where('id', $category->id)
            ->withTrashed()
            ->get()
            ->first();
        $this->assertNotNull($deletedCategory->deleted_at);
    }
}
