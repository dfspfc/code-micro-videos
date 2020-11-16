<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Traits\Uuid;
use App\Models\Video;
use App\Models\Gender;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Illuminate\Http\Request;
use Tests\Exceptions\TestTransactionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VideoTest extends TestCase
{
    use DatabaseMigrations, JsonFragmentValidation;

    public function testListShouldReturn200()
    {
        $video = factory(Video::class)->create()->toArray();
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$video]);
    }

    public function testShowSpecificVideoShouldReturn200()
    {
        $video = factory(Video::class)->create();
        $response = $this->get(route('videos.show', ['video' => $video->id]));
        
        $response
            ->assertStatus(200)
            ->assertJson($video->toArray());
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
        $response = $this->json(
            'POST',
            route('videos.store'),
            ['title' => str_repeat('a', 256)]
        );

        $response->assertStatus(422);
        $this->assertMax255($response, 'title');
    }

    public function testCreatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('videos.store'),
            [
                'opened' => 'a',
            ]
        );

        $response->assertStatus(422);
        $this->assertBoolean($response, 'opened');
    }

    public function testCreatePassingAttributesDifferentFromIntegerShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('videos.store'),
            [
                'duration' => 'not an integer',
            ]
        );

        $response->assertStatus(422);
        $this->assertInteger($response, 'duration');
    }

    public function testCreatePassingAttributesDifferentFromWhatIsAllowedShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('videos.store'),
            [
                'rating' => 'invalid',
            ]
        );

        $response->assertStatus(422);
        $this->assertNotIn($response, 'rating');
    }

    public function testCreatePassingAttributesWithInvalidYearShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('videos.store'),
            ['year_launched' => 'not a year']
        );

        $response->assertStatus(422);
        $this->assertYear($response, 'year_launched');
    }

    public function testCreatePassingAttributesDifferentFromArrayShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('videos.store'),
            [
                'categories_id' => 'not an array',
                'genders_id' => 'not an array'
            ]
        );

        $response->assertStatus(422);
        $this->assertArray($response, 'categories_id');
        $this->assertArray($response, 'genders_id');
    }

    public function testCreatePassingAttributesRelatedOnDatabaseButThatDoNotExistsThereShouldReturn422()
    {
        $response = $this->json(
            'POST',
            route('videos.store'),
            [
                'categories_id' => [Uuid::newVersion4()],
                'genders_id' => [Uuid::newVersion4()],
            ]
        );

        $response->assertStatus(422);
        $this->assertNotInDatabase($response, 'categories_id');
        $this->assertNotInDatabase($response, 'genders_id');
    }

    public function testUpdateNotPassingAnyAttributeShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
            []
        );
        
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
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
            ['title' => str_repeat('a', 256)]
        );

        $response->assertStatus(422);
        $this->assertMax255($response, 'title');
    }

    public function testUpdatePassingAttributesDifferentFromBooleanShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
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
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
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
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
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
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
            ['year_launched' => 'not a year']
        );

        $response->assertStatus(422);
        $this->assertYear($response, 'year_launched');
    }

    public function testUpdatePassingAttributesDifferentFromArrayShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
            [
                'categories_id' => 'not an array',
                'genders_id' => 'not an array'
            ]
        );

        $response->assertStatus(422);
        $this->assertArray($response, 'categories_id');
        $this->assertArray($response, 'genders_id');
    }

    public function testUpdatePassingAttributesRelatedOnDatabaseButThatDoNotExistsThereShouldReturn422()
    {
        $video = factory(Video::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
            [
                'categories_id' => [Uuid::newVersion4()],
                'genders_id' => [Uuid::newVersion4()]
            ]
        );

        $response->assertStatus(422);
        $this->assertNotInDatabase($response, 'categories_id');
        $this->assertNotInDatabase($response, 'genders_id');
    }

    public function testCreatePassingFileAttributesLargerThanWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4')->size('600000');

        $response = $this->json(
            'POST',
            route('videos.store'),
            ['video_file' => $file]
        );

        $response->assertStatus(422);
        $this->assertMaxFileSize($response, 'video_file', 500000);
    }

    public function testCreatePassingFileTypesDifferentFromWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp3')->size('500000');

        $response = $this->json(
            'POST',
            route('videos.store'),
            ['video_file' => $file]
        );

        $response->assertStatus(422);
        $this->assertFileType($response, 'video_file', 'mp4');
    }

    public function testUpdatePassingFileAttributesLargerThanWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4')->size('600000');

        $video = factory(Video::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
            ['video_file' => $file]
        );

        $response->assertStatus(422);
        $this->assertMaxFileSize($response, 'video_file', 500000);
    }

    public function testUpdatePassingFileTypesDifferentFromWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.jpeg')->size('500000');

        $video = factory(Video::class)->create();
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
            ['video_file' => $file]
        );

        $response->assertStatus(422);
        $this->assertFileType($response, 'video_file', 'mp4');
    }

    public function testCreatePassingAllFieldsShouldCreateAndReturn201()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4')->size('500000');

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
            'video_file' => $file,
        ];
        $response = $this->json(
            'POST',
            route('videos.store'),
            $requestBody
        );
        
        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'title' => $requestBody['title'],
                'description' => $requestBody['description'],
                'year_launched' => $requestBody['year_launched'],
                'opened' => $requestBody['opened'],
                'rating' => $requestBody['rating'],
                'duration' => $requestBody['duration'],
                'video_file' => $file->hashName(),
            ]);    
        $this->assertDatabaseHas(
            'category_video',
            [
                'category_id' => $relatedCategory->id,
                'video_id' => $response->json()['id'],
            ]
        );
        $this->assertDatabaseHas(
            'gender_video',
            [
                'gender_id' => $relatedGender->id,
                'video_id' => $response->json()['id'],
            ]
        );
   
        $this->assertDatabaseHas(
            'videos',
            [
                'video_file' => $file->hashName(),
            ]
        );
        Storage::assertExists(
            "{$response->json()['id']}/{$file->hashName()}"
        );
    }

    public function testUpdateShouldUpdateAndReturn200()
    {
        Storage::fake();

        $formerRelatedFile = UploadedFile::fake()->create('former_video.mp4')->size('500000');
        $formerRelatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $formerRelatedGender = factory(Gender::class)->create(['name' => 'related gender']);
        $video = factory(Video::class)->create([
            'title' => 'title test to be updated',
            'description' => 'description test to be updated',
            'year_launched' => 2008,
            'opened' => true,
            'rating' => 'L',
            'duration' => 20,
            'video_file' => $formerRelatedFile,
        ]);
        $video->categories()->sync($formerRelatedCategory->id);
        $video->genders()->sync($formerRelatedGender->id);

        $updatedRelatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $updatedRelatedGender = factory(Gender::class)->create(['name' => 'related gender']);
        $updatedFile = UploadedFile::fake()->create('video.mp4')->size('500000');
        $updateRequestBody = [
            'title' => 'updated title test',
            'description' => 'updated description test',
            'year_launched' => 2009,
            'opened' => false,
            'rating' => '10',
            'duration' => 29,
            'categories_id' => [$updatedRelatedCategory->id],
            'genders_id' => [$updatedRelatedGender->id],
            'video_file' => $updatedFile
        ];
        $response = $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $video->id],
            ),
            $updateRequestBody
        );
        
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'title' => $updateRequestBody['title'],
                'description' => $updateRequestBody['description'],
                'year_launched' => $updateRequestBody['year_launched'],
                'opened' => $updateRequestBody['opened'],
                'rating' => $updateRequestBody['rating'],
                'duration' => $updateRequestBody['duration'],
                'video_file' => $updatedFile->hashName(),
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
                'video_id' => $response->json()['id'],
            ]
        );
        $this->assertDatabaseHas(
            'gender_video',
            [
                'gender_id' => $updatedRelatedGender->id,
                'video_id' => $response->json()['id'],
            ]
        );

        $this->assertDatabaseMissing(
            'videos',
            [
                'video_file' => $formerRelatedFile->hashName(),
            ]
        );
        $this->assertDatabaseHas(
            'videos',
            [
                'video_file' => $updatedFile->hashName(),
            ]
        );
        Storage::assertMissing(
            "{$response->json()['id']}/{$formerRelatedFile->hashName()}"
        );
        Storage::assertExists(
            "{$response->json()['id']}/{$updatedFile->hashName()}"
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
