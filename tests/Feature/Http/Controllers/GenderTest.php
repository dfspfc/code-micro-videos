<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Gender;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Lang;

class GenderTest extends TestCase
{
    use DatabaseMigrations;

    public function testListShouldReturn200()
    {
        $gender1 = factory(Gender::class)->create();
        $gender2 = factory(Gender::class)->create();
        $expected = [$gender1->toArray(), $gender2->toArray()];
        $response = $this->get(route('genders.index'));
        
        $response
            ->assertStatus(200)
            ->assertJson($expected);
    }

    public function testShowSpecificGenderShouldReturn200()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->get(route('genders.show', ['gender' => $gender->id]));
        
        $response
            ->assertStatus(200)
            ->assertJson($gender->toArray());
    }

    public function testCreateNotPassingAttributeNameShouldReturn422()
    {
        $response = $this->json('POST', route('genders.store'), []);
        $this->assertNameRequired($response);
    }

    public function testCreatePassingAttributeNameLargerThan255CharactersReturn422()
    {
        $response = $this->json(
            'POST',
            route('genders.store'),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertNameMaxCharacters($response);
    }

    public function testCreatePassingAttributeIsActiveDifferentFromBooleanShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('genders.store'),
            [
                'name' => 'valid name',
                'is_active' => 'a',
            ]
        );
        $this->assertIsActiveIsInvalid($response);
    }

    public function testUpdateNotPassingAttributeNameShouldReturn422()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'genders.update',
                ['gender' => $gender->id],
            ),
            []
        );
        $this->assertNameRequired($response);
    }

    public function testUpdatePassingAttributeNameLargerThan255CharactersReturn422()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'genders.update',
                ['gender' => $gender->id],
            ),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertNameMaxCharacters($response);
    }

    public function testUpdatePassingAttributeIsActiveDifferentFromBooleanShouldReturn422()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'genders.update',
                ['gender' => $gender->id],
            ),
            [
                'name' => 'valid name',
                'is_active' => 'a',
            ]
        );
        $this->assertIsActiveIsInvalid($response);
    }

    public function testCreatePassingAttributeNameShouldCreateWithAllDefaultFieldsAndReturn201()
    {
        $response = $this->json(
            'POST',
            route('genders.store'),
            ['name' => 'valid name']
        );
        $id = $response->json('id');
        $gender = Gender::find($id);
        
        $response
            ->assertStatus(201)
            ->assertJson($gender->toArray());
        $this->assertTrue($response->json('is_active'));
    }

    public function testCreatePassingAllFiledsShouldCreateAndReturn201()
    {
        $requestBody = [
            'name' => 'valid name',
            'is_active' => false,
        ];
        $response = $this->json(
            'POST',
            route('genders.store'),
            $requestBody
        );
        
        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => $requestBody['name'],
                'is_active' => $requestBody['is_active'],
            ]);
    }

    public function testUpdateShouldUpdateAndReturn200()
    {
        $gender = factory(Gender::class)->create([
            'name' => 'name to be updated',
            'is_active' => false,
        ]);
        $updateRequestBody = [
            'name' => 'new name',
            'is_active' => true,
        ];
        $response = $this->json(
            'PUT',
            route(
                'genders.update',
                ['gender' => $gender->id],
            ),
            $updateRequestBody
        );
        
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => $updateRequestBody['name'],
                'is_active' => $updateRequestBody['is_active'],
            ]);
    }

    public function testDeleteShouldSoftDeleteGenderAndReturn204()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->json(
            'DELETE',
            route(
                'genders.destroy',
                ['gender' => $gender->id],
            ),
            []
        );
        $response->assertStatus(204);

        $deletedGender = Gender::where('id', $gender->id)
            ->withTrashed()
            ->get()
            ->first();
        $this->assertNotNull($deletedGender->deleted_at);
    }

    private function assertNameRequired($response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    private function assertNameMaxCharacters($response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    private function assertIsActiveIsInvalid($response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }
}
