<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\Api\GenderController;
use App\Models\Gender;
use App\Http\Resources\Gender as GenderResource;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Traits\Uuid;
use Mockery;
use Tests\Exceptions\TestTransactionException;
use Illuminate\Http\Request;

class GenderTest extends TestCase
{
    use DatabaseMigrations, JsonFragmentValidation;

    private $gender;

    private $fieldsSerialized = [
        'id',
        'name',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->gender = factory(Gender::class)->create();
    }

    public function testListShouldReturn200()
    {
        $response = $this->get(route('genders.index'));
        
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => $this->fieldsSerialized
                    ],
                    'meta' => [],
                    'links' => []
                ]
            );
            
        $resource = GenderResource::collection(collect([$this->gender]));
        $response->assertJson($resource->response()->getData(true));
    }
    

    public function testShowSpecificGenderShouldReturn200()
    {
        $response = $this->get(route('genders.show', ['gender' => $this->gender->id]));
        
        $resource = new GenderResource(Gender::find($this->gender->id));
        $response
            ->assertStatus(200)
            ->assertJson($resource->response()->getData(true));
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

    public function testCreatePassingAttributesDifferentFromArrayShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('genders.store'),
            [
                'categories_id' => 'not an array'
            ]
        );

        $response->assertStatus(422);
        $this->assertArray($response, 'categories_id');
    }

    public function testCreatePassingAttributesRelatedOnDatabaseButThatDoNotExistsThereShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('genders.store'),
            [
                'categories_id' => [Uuid::newVersion4()]
            ]
        );

        $response->assertStatus(422);
        $this->assertNotInDatabase($response, 'categories_id');
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

    public function testUpdatePassingAttributesDifferentFromArrayShouldReturn422()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'genders.update',
                ['gender' => $gender->id],
            ),
            [
                'categories_id' => 'not an array'
            ]
        );

        $response->assertStatus(422);
        $this->assertArray($response, 'categories_id');
    }

    public function testUpdatePassingAttributesRelatedOnDatabaseButThatDoNotExistsThereShouldReturn422()
    {
        $gender = factory(Gender::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'genders.update',
                ['gender' => $gender->id],
            ),
            [
                'categories_id' => [Uuid::newVersion4()]
            ]
        );

        $response->assertStatus(422);
        $this->assertNotInDatabase($response, 'categories_id');
    }

    public function testCreatePassingRequiredAttributesShouldCreateWithAllDefaultFieldsAndReturn201()
    {
        $relatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $response = $this->json(
            'POST',
            route('genders.store'),
            [
                'name' => 'valid name',
                'categories_id' => [$relatedCategory->id]
            ]
        );
        $genderId = $response->json('data.id');
        $gender = Gender::find($genderId);
        
        $resource = new GenderResource(Gender::find($gender->id));
        $response
            ->assertStatus(201)
            ->assertJson($resource->response()->getData(true));

        $this->assertTrue($response->json('data.is_active'));
        $this->assertDatabaseHas(
            'category_gender',
            [
                'category_id' => $relatedCategory->id,
                'gender_id' => $genderId,
            ]
        );
    }

    public function testCreatePassingAllFieldsShouldCreateAndReturn201()
    {
        $relatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $requestBody = [
            'name' => 'valid name',
            'is_active' => false,
            'categories_id' => [$relatedCategory->id]
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

        $this->assertDatabaseHas(
            'category_gender',
            [
                'category_id' => $relatedCategory->id,
                'gender_id' => $response->json('data.id'),
            ]
        );
    }

    public function testUpdateShouldUpdateAndReturn200()
    {
        $formerRelatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $gender = factory(Gender::class)->create([
            'name' => 'name to be updated',
            'is_active' => false
        ]);
        $gender->categories()->sync($formerRelatedCategory->id);
        $updatedRelatedCategory = factory(Category::class)->create(['name' => 'updated related category']);
        $updateRequestBody = [
            'name' => 'new name',
            'is_active' => true,
            'categories_id' => [$updatedRelatedCategory->id],
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
        $this->assertDatabaseMissing(
            'category_gender',
            [
                'category_id' => $formerRelatedCategory->id,
                'gender_id' => $gender->id,
            ]
        );
        $this->assertDatabaseHas(
            'category_gender',
            [
                'category_id' => $updatedRelatedCategory->id,
                'gender_id' => $gender->id,
            ]
        );
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

    public function testMakeRollbackWhenCreationFailsIntheMiddleOfTheTransaction()
    {
        $categoryMock = Mockery::mock(Category::class);
        $categoryMock->shouldReceive('sync')
            ->once()
            ->andThrow(new TestTransactionException());

        $genderModelMock = Mockery::mock(Gender::class);
        $genderModelMock->shouldReceive('categories')
            ->once()
            ->andReturn($categoryMock);
        $genderModelMock->shouldReceive('create')
            ->once()
            ->andReturn($genderModelMock);

        $controller = Mockery::mock(GenderController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->AndReturnTrue();
        $controller
            ->shouldReceive('storeRules')
            ->withAnyArgs()
            ->AndReturn([]);
        $controller
            ->shouldReceive('model')
            ->andReturn($genderModelMock);

        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('get')
            ->andReturn('');

        $hasError = false;
        try {
            $hasError = true;
            $controller->store($request);
        } catch (TestTransactionException $e) {
            $this->assertCount(1, Gender::all());
        }
        $this->assertTrue($hasError);
    }

    public function testMakeRollbackWhenUpdateFailsIntheMiddleOfTheTransaction()
    {
        $genderOnDB = factory(Gender::class)->create([
            'name' => 'name that should not change',
            'is_active' => false
        ]);

        $categoryMock = Mockery::mock(Category::class);
        $categoryMock->shouldReceive('sync')
            ->once()
            ->andThrow(new TestTransactionException());

        $genderModelMock = Mockery::mock(Gender::class);
        $genderModelMock->shouldReceive('categories')
            ->once()
            ->andReturn($categoryMock);
        $genderModelMock->shouldReceive('update')
            ->once()
            ->andReturn($genderModelMock);

        $controller = Mockery::mock(GenderController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->AndReturn([]);
            $controller
            ->shouldReceive('findObjectFromModel')
            ->withAnyArgs()
            ->AndReturn($genderModelMock);
        $controller
            ->shouldReceive('updateRules')
            ->withAnyArgs()
            ->AndReturn([]);

        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('get')
            ->andReturn('');

        $hasError = false;
        try {
            $hasError = true;
            $controller->update($request, $genderOnDB->id);
        } catch (TestTransactionException $e) {
            $updatedGenderOnDB = Gender::where('id', $genderOnDB->id)
                ->get()
                ->first()
                ->refresh()
                ->toArray();
            $this->assertEquals(
                $genderOnDB->refresh()->toArray(),
                $updatedGenderOnDB
            );
        }
        $this->assertTrue($hasError);
    }
}
