<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\CastMember;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Lang;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testListShouldReturn200()
    {
        $castMember = factory(CastMember::class)->create()->toArray();
        $response = $this->get(route('cast_members.index'));
        
        $response
            ->assertStatus(200)
            ->assertJson([$castMember]);
    }

    public function testShowSpecificCastMemberShouldReturn200()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->get(route('cast_members.show', ['cast_member' => $castMember->id]));
        
        $response
            ->assertStatus(200)
            ->assertJson($castMember->toArray());
    }

    public function testCreateNotPassingAttributeNameShouldReturn422()
    {
        $response = $this->json('POST', route('cast_members.store'), []);
        $this->assertNameRequired($response);
    }

    public function testCreatePassingAttributeNameLargerThan255CharactersReturn422()
    {
        $response = $this->json(
            'POST',
            route('cast_members.store'),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertNameMaxCharacters($response);
    }

    public function testCreatePassingAttributeTypeDifferentFromWhatIsAllowedShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('cast_members.store'),
            [
                'name' => 'valid name',
                'type' => 3,
            ]
        );
        $this->assertTypeInvalid($response);
    }

    public function testUpdateNotPassingAttributeNameShouldReturn422()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'cast_members.update',
                ['cast_member' => $castMember->id],
            ),
            []
        );
        $this->assertNameRequired($response);
    }

    public function testUpdatePassingAttributeNameLargerThan255CharactersReturn422()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'cast_members.update',
                ['cast_member' => $castMember->id],
            ),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertNameMaxCharacters($response);
    }

    public function testUpdatePassingAttributeTypeDifferentFromWhatIsAllowedShouldReturn422()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'cast_members.update',
                ['cast_member' => $castMember->id],
            ),
            [
                'name' => 'valid name',
                'type' => 3,
            ]
        );
        $this->assertTypeInvalid($response);
    }

    public function testCreatePassingAllFieldsShouldCreateAndReturn201()
    {
        $requestBody = [
            'name' => 'valid name',
            'type' => CastMember::TYPE_DIRECTOR,
        ];
        $response = $this->json(
            'POST',
            route('cast_members.store'),
            $requestBody
        );
        
        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => $requestBody['name'],
                'type' => $requestBody['type'],
            ]);
    }

    public function testUpdateShouldUpdateAndReturn200()
    {
        $castMember = factory(CastMember::class)->create([
            'name' => 'name to be updated',
            'type' => CastMember::TYPE_DIRECTOR,
        ]);
        $updateRequestBody = [
            'name' => 'new name',
            'type' => CastMember::TYPE_ACTOR,
        ];
        $response = $this->json(
            'PUT',
            route(
                'cast_members.update',
                ['cast_member' => $castMember->id],
            ),
            $updateRequestBody
        );
        
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => $updateRequestBody['name'],
                'type' => $updateRequestBody['type'],
            ]);
    }

    public function testDeleteShouldSoftDeleteCastMemberAndReturn204()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->json(
            'DELETE',
            route(
                'cast_members.destroy',
                ['cast_member' => $castMember->id],
            ),
            []
        );
        $response->assertStatus(204);

        $deletedCastMember = CastMember::where('id', $castMember->id)
            ->withTrashed()
            ->get()
            ->first();
        $this->assertNotNull($deletedCastMember->deleted_at);
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

    private function assertTypeInvalid($response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type'])
            ->assertJsonFragment([
                Lang::get('validation.in', ['attribute' => 'type'])
            ]);
    }
}
