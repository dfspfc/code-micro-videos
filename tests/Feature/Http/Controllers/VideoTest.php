<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Traits\Uuid;
use App\Models\Video;
use App\Http\Resources\Video as VideoResource;
use App\Models\Gender;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VideoTest extends TestCase
{
    use DatabaseMigrations, JsonFragmentValidation, VideoBase;

    private $video;

    private $fieldsSerialized = [
        'id',
        'title',
        'description',
        'year_launched',
        'rating',
        'duration',
        'rating',
        'opened',
        'thumb_file',
        'banner_file',
        'video_file',
        'trailer_file',
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
        ],
        'genders' => [
            '*' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
    }

    public function testListShouldReturn200()
    {
        $response = $this->get(route('videos.index'));
        
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
            
        $resource = VideoResource::collection(collect([$this->video]));
        $response->assertJson($resource->response()->getData(true));
    }

    public function testShowSpecificVideoShouldReturn200()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        
        $resource = new VideoResource(Video::find($this->video->id));
        $response
            ->assertStatus(200)
            ->assertJson($resource->response()->getData(true));
    }

    public function testCreatePassingNoAttributesShouldReturn422()
    {
        $response = $this->json('POST', route('videos.store'), []);
        
        $response->assertStatus(422);
        $this->assertRequired($response, 'title');
        $this->assertRequired($response, 'description');
        $this->assertRequired($response, 'year_launched');
        $this->assertRequired($response, 'rating');
        $this->assertRequired($response, 'duration');
        $this->assertRequired($response, 'categories_id');
        $this->assertRequired($response, 'genders_id');
    }
    
    public function testCreatePassingAttributesLargerThan255CharactersShouldReturn422()
    {
        $response = $this->postVideo([
            'title' => str_repeat('a', 256)
        ]);

        $response->assertStatus(422);
        $this->assertMax255($response, 'title');
    }

    public function testCreatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $response = $this->postVideo([
            'opened' => 'a',
        ]);

        $response->assertStatus(422);
        $this->assertBoolean($response, 'opened');
    }

    public function testCreatePassingAttributesDifferentFromIntegerShouldReturn422()
    {
        $response = $this->postVideo([
            'duration' => 'not an integer',
        ]);

        $response->assertStatus(422);
        $this->assertInteger($response, 'duration');
    }

    public function testCreatePassingAttributesDifferentFromWhatIsAllowedShouldReturn422()
    {
        $response = $this->postVideo([
            'rating' => 'invalid',
        ]);

        $response->assertStatus(422);
        $this->assertNotIn($response, 'rating');
    }

    public function testCreatePassingAttributesWithInvalidYearShouldReturn422()
    {
        $response = $this->postVideo([
            'year_launched' => 'not a year',
        ]);

        $response->assertStatus(422);
        $this->assertYear($response, 'year_launched');
    }

    public function testCreatePassingAttributesDifferentFromArrayShouldReturn422()
    {
        $response = $this->postVideo([
            'categories_id' => 'not an array',
            'genders_id' => 'not an array',
        ]);

        $response->assertStatus(422);
        $this->assertArray($response, 'categories_id');
        $this->assertArray($response, 'genders_id');
    }

    public function testCreatePassingAttributesRelatedOnDatabaseButThatDoNotExistsThereShouldReturn422()
    {
        $response = $this->postVideo([
            'categories_id' => [Uuid::newVersion4()],
            'genders_id' => [Uuid::newVersion4()],
        ]);

        $response->assertStatus(422);
        $this->assertNotInDatabase($response, 'categories_id');
        $this->assertNotInDatabase($response, 'genders_id');
    }

    public function testUpdateNotPassingAnyAttributeShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->updateVideo($video->id, []);
        
        $response->assertStatus(422);
        $this->assertRequired($response, 'title');
        $this->assertRequired($response, 'description');
        $this->assertRequired($response, 'year_launched');
        $this->assertRequired($response, 'rating');
        $this->assertRequired($response, 'duration');
        $this->assertRequired($response, 'categories_id');
        $this->assertRequired($response, 'genders_id');
    }

    public function testUpdatePassingAttributesLargeThan255CharactersShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'title' => str_repeat('a', 256),
            ]
        );

        $response->assertStatus(422);
        $this->assertMax255($response, 'title');
    }

    public function testUpdatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'opened' => 'a',
            ]
        );

        $response->assertStatus(422);
        $this->assertBoolean($response, 'opened');
    }

    public function testUpdatePassingAttributesDifferentFromIntegerShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'duration' => 'not an integer',
            ]
        );

        $response->assertStatus(422);
        $this->assertInteger($response, 'duration');
    }

    public function testUpdatePassingAttributesDifferentFromWhatIsAllowedShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'rating' => 'invalid',
            ]
        );

        $response->assertStatus(422);
        $this->assertNotIn($response, 'rating');
    }

    public function testUpdatePassingAttributesWithInvalidYearShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'year_launched' => 'not a year',
            ]
        );

        $response->assertStatus(422);
        $this->assertYear($response, 'year_launched');
    }

    public function testUpdatePassingAttributesDifferentFromArrayShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'categories_id' => 'not an array',
                'genders_id' => 'not an array',
            ]
        );

        $response->assertStatus(422);
        $this->assertArray($response, 'categories_id');
        $this->assertArray($response, 'genders_id');
    }

    public function testUpdatePassingAttributesRelatedOnDatabaseButThatDoNotExistsThereShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'categories_id' => [Uuid::newVersion4()],
                'genders_id' => [Uuid::newVersion4()]
            ]
        );

        $response->assertStatus(422);
        $this->assertNotInDatabase($response, 'categories_id');
        $this->assertNotInDatabase($response, 'genders_id');
    }

    public function testCreatePassingAllFieldsShouldCreateAndReturn201()
    {
        $relatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $relatedGender = factory(Gender::class)->create(['name' => 'related gender']);
        $requestBody = [
            'title' => 'title test',
            'description' => 'description test',
            'year_launched' => 2009,
            'opened' => true,
            'rating' => 'L',
            'duration' => 20,
            'categories_id' => [$relatedCategory->id],
            'genders_id' => [$relatedGender->id],
        ];
        $response = $this->postVideo($requestBody);
        
        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'title' => $requestBody['title'],
                'description' => $requestBody['description'],
                'year_launched' => $requestBody['year_launched'],
                'opened' => $requestBody['opened'],
                'rating' => $requestBody['rating'],
                'duration' => $requestBody['duration'],
            ]);    
        $this->assertDatabaseHas(
            'category_video',
            [
                'category_id' => $relatedCategory->id,
                'video_id' => $response->json('data.id'),
            ]
        );
        $this->assertDatabaseHas(
            'gender_video',
            [
                'gender_id' => $relatedGender->id,
                'video_id' => $response->json('data.id'),
            ]
        );
    }

    public function testUpdateShouldUpdateAndReturn200()
    {
        $formerRelatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $formerRelatedGender = factory(Gender::class)->create(['name' => 'related gender']);
        $video = factory(Video::class)->create([
            'title' => 'title test to be updated',
            'description' => 'description test to be updated',
            'year_launched' => 2008,
            'opened' => true,
            'rating' => 'L',
            'duration' => 20,
        ]);
        $video->categories()->sync($formerRelatedCategory->id);
        $video->genders()->sync($formerRelatedGender->id);

        $updatedRelatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $updatedRelatedGender = factory(Gender::class)->create(['name' => 'related gender']);
        $updateRequestBody = [
            'title' => 'updated title test',
            'description' => 'updated description test',
            'year_launched' => 2009,
            'opened' => false,
            'rating' => '10',
            'duration' => 29,
            'categories_id' => [$updatedRelatedCategory->id],
            'genders_id' => [$updatedRelatedGender->id],
        ];
        $response = $this->updateVideo($video->id, $updateRequestBody);
        
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'title' => $updateRequestBody['title'],
                'description' => $updateRequestBody['description'],
                'year_launched' => $updateRequestBody['year_launched'],
                'opened' => $updateRequestBody['opened'],
                'rating' => $updateRequestBody['rating'],
                'duration' => $updateRequestBody['duration'],
            ]);
        $this->assertDatabaseMissing(
            'category_video',
            [
                'category_id' => $formerRelatedCategory->id,
                'video_id' => $video->id,
            ]
        );
        $this->assertDatabaseMissing(
            'gender_video',
            [
                'gender_id' => $formerRelatedGender->id,
                'video_id' => $video->id,
            ]
        );
        $this->assertDatabaseHas(
            'category_video',
            [
                'category_id' => $updatedRelatedCategory->id,
                'video_id' => $response->json('data.id'),
            ]
        );
        $this->assertDatabaseHas(
            'gender_video',
            [
                'gender_id' => $updatedRelatedGender->id,
                'video_id' => $response->json('data.id'),
            ]
        );
    }

    public function testDeleteShouldSoftDeleteVideoAndReturn204()
    {
        $video = factory(Video::class)->create();
        $response = $this->json(
            'DELETE',
            route(
                'videos.destroy',
                ['video' => $video->id],
            ),
            []
        );
        $response->assertStatus(204);

        $deletedVideo = Video::where('id', $video->id)
            ->withTrashed()
            ->get()
            ->first();
        $this->assertNotNull($deletedVideo->deleted_at);
    }
}
