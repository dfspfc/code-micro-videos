<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\CastMember;
use App\Http\Resources\CastMember as CastMemberResource;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations, JsonFragmentValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create();
    }

    public function testListShouldReturn200()
    {
        $response = $this->get(route('cast_members.index'));
        
        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShowSpecificCastMemberShouldReturn200()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));
        
        $resource = new CastMemberResource(CastMember::find($this->castMember->id));
        $response
            ->assertStatus(200)
            ->assertJson($resource->response()->getData(true));
    }

    public function testCreatePassingNoAttributesShouldReturn422()
    {
        $response = $this->json('POST', route('cast_members.store'), []);
        $this->assertRequired($response, 'name');
    }

    public function testCreatePassingAttributesLargerThan255CharactersShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('cast_members.store'),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertMax255($response, 'name');
    }

    public function testCreatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('cast_members.store'),
            [
                'type' => 'invalid type',
            ]
        );
        $this->assertNotIn($response, 'type');
    }

    public function testUpdateNotPassingAnyAttributeShouldReturn422()
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
        $this->assertRequired($response, 'name');
    }

    public function testUpdatePassingAttributesLargeThan255CharactersShouldReturn422()
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
        $this->assertMax255($response, 'name');
    }

    public function testUpdatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'cast_members.update',
                ['cast_member' => $castMember->id],
            ),
            [
                'type' => 'not valid',
            ]
        );
        $this->assertNotIn($response, 'type');
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
}
