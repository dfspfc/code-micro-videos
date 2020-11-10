<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Gender;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GenderTest extends TestCase
{
    use DatabaseMigrations, JsonFragmentValidation;

    public function testListShouldReturn200()
    {
        $gender = factory(Gender::class)->create()->toArray();
        $response = $this->get(route('genders.index'));
        
        $response
            ->assertStatus(200)
            ->assertJson([$gender]);
    }

    public function testShowSpecificGenderShouldReturn200()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->get(route('genders.show', ['gender' => $gender->id]));
        
        $response
            ->assertStatus(200)
            ->assertJson($gender->toArray());
    }

    public function testCreatePassingNoAttributesShouldReturn422()
    {
        $response = $this->json('POST', route('genders.store'), []);
        $this->assertRequired($response, 'name');
    }

    public function testCreatePassingAttributesLargerThan255CharactersShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('genders.store'),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertMax255($response, 'name');
    }

    public function testCreatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('genders.store'),
            [
                'name' => 'valid name',
                'is_active' => 'a',
            ]
        );
        $this->assertBoolean($response, 'is_active');
    }

    public function testUpdateNotPassingAnyAttributeShouldReturn422()
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
        $this->assertRequired($response, 'name');
    }

    public function testUpdatePassingAttributesLargeThan255CharactersShouldReturn422()
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
        $this->assertMax255($response, 'name');
    }

    public function testUpdatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'genders.update',
                ['gender' => $gender->id],
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
}
