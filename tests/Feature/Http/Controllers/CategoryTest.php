<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\Category as CategoryResource;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CategoryTest extends TestCase
{
    use DatabaseMigrations, JsonFragmentValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testListShouldReturn200()
    {
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testShowSpecificCategoryShouldReturn200()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        
        $resource = new CategoryResource(Category::find($this->category->id));
        $response
            ->assertStatus(200)
            ->assertJson($resource->response()->getData(true));
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
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $this->category->id],
            ),
            []
        );
        $this->assertRequired($response, 'name');
    }

    public function testUpdatePassingAttributesLargeThan255CharactersShouldReturn422()
    {
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $this->category->id],
            ),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertMax255($response, 'name');
    }

    public function testUpdatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $this->category->id],
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
        $id = $response->json('data.id');
        $category = Category::find($id);
        
        $response
            ->assertStatus(201)
            ->assertJson(['data' => $category->toArray()]);
        $this->assertTrue($response->json('data.is_active'));
        $this->assertNull($response->json('data.description'));
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
        $response = $this->json(
            'DELETE',
            route(
                'categories.destroy',
                ['category' => $this->category->id],
            ),
            []
        );
        $response->assertStatus(204);

        $deletedCategory = Category::where('id', $this->category->id)
            ->withTrashed()
            ->get()
            ->first();
        $this->assertNotNull($deletedCategory->deleted_at);
    }
}
