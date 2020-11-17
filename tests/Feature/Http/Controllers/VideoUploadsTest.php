<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use App\Models\Gender;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VideoUploadsTest extends TestCase
{
    use DatabaseMigrations, JsonFragmentValidation, VideoBase;

    public function testCreatePassingFileAttributesLargerThanWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4')->size('600000');
        $thumb = UploadedFile::fake()->create('thumb.jpg')->size('600000');

        $response = $this->postVideo([
            'video_file' => $file,
            'thumb_file' => $thumb
        ]);

        $response->assertStatus(422);
        $this->assertMaxFileSize($response, 'video_file', 500000);
        $this->assertMaxFileSize($response, 'thumb_file', 500000);
    }

    public function testCreatePassingFileTypesDifferentFromWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp3')->size('500000');
        $thumb = UploadedFile::fake()->image('thumb.png')->size('500000');

        $response = $this->postVideo([
            'video_file' => $file,
            'thumb_file' => $thumb,
        ]);

        $response->assertStatus(422);
        $this->assertFileType($response, 'video_file', 'mp4');
        $this->assertFileType($response, 'thumb_file', 'jpg, jpeg');
    }

    public function testUpdatePassingFileAttributesLargerThanWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4')->size('600000');
        $thumb = UploadedFile::fake()->create('thumb.jpg')->size('600000');

        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'video_file' => $file,
                'thumb_file' => $thumb,
            ]
        );

        $response->assertStatus(422);
        $this->assertMaxFileSize($response, 'video_file', 500000);
        $this->assertMaxFileSize($response, 'thumb_file', 500000);
    }

    public function testUpdatePassingFileTypesDifferentFromWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.jpeg')->size('500000');
        $thumb = UploadedFile::fake()->image('thumb.png')->size('500000');

        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'video_file' => $file,
                'thumb_file' => $thumb,
            ]
        );

        $response->assertStatus(422);
        $this->assertFileType($response, 'video_file', 'mp4');
        $this->assertFileType($response, 'thumb_file', 'jpg, jpeg');
    }

    public function testCreatePassingAllFileFieldsShouldCreateAndUploadAllFilesReturn201()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4')->size('1000');
        $thumb = UploadedFile::fake()->image('thumb.jpg')->size('1000');
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
            'thumb_file' => $thumb,
        ];
        $response = $this->postVideo($requestBody);

        $response
            ->assertStatus(201)
            ->assertJsonFragment(['video_file' => $file->hashName()]);
        $response
            ->assertStatus(201)
            ->assertJsonFragment(['thumb_file' => $thumb->hashName()]);
        Storage::assertExists(
            "{$response->json()['id']}/{$file->hashName()}"
        );
        Storage::assertExists(
            "{$response->json()['id']}/{$thumb->hashName()}"
        );
    }

    public function testUpdateShouldUpdateThenUploadTheNewFilesThenDeleteOldFilesAndReturn200()
    {
        Storage::fake();

        $formerRelatedFile = UploadedFile::fake()->create('former_video.mp4')->size('1000');
        $formerRelatedThumb = UploadedFile::fake()->image('thumb.jpg')->size('1000');
        $video = factory(Video::class)->create([
            'title' => 'title test to be updated',
            'description' => 'description test to be updated',
            'year_launched' => 2008,
            'opened' => true,
            'rating' => 'L',
            'duration' => 20,
            'video_file' => $formerRelatedFile,
            'thumb_file' => $formerRelatedThumb,
        ]);

        $updatedRelatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $updatedRelatedGender = factory(Gender::class)->create(['name' => 'related gender']);
        $updatedFile = UploadedFile::fake()->create('video.mp4')->size('1000');
        $updatedThumb = UploadedFile::fake()->create('thumb.jpg')->size('1000');
        $updateRequestBody = [
            'title' => 'updated title test',
            'description' => 'updated description test',
            'year_launched' => 2009,
            'opened' => false,
            'rating' => '10',
            'duration' => 29,
            'categories_id' => [$updatedRelatedCategory->id],
            'genders_id' => [$updatedRelatedGender->id],
            'video_file' => $updatedFile,
            'thumb_file' => $updatedThumb,
        ];
        $response = $this->updateVideo($video->id, $updateRequestBody);
        
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'video_file' => $updatedFile->hashName(),
                'thumb_file' => $updatedThumb->hashName(),
            ]);
        $this->assertDatabaseMissing(
            'videos',
            [
                'video_file' => $formerRelatedFile->hashName(),
                'thumb_file' => $formerRelatedThumb->hashName(),
            ]
        );
        $this->assertDatabaseHas(
            'videos',
            [
                'video_file' => $updatedFile->hashName(),
                'thumb_file' => $updatedThumb->hashName(),
            ]
        );
        Storage::assertMissing(
            "{$response->json()['id']}/{$formerRelatedFile->hashName()}"
        );
        Storage::assertMissing(
            "{$response->json()['id']}/{$formerRelatedThumb->hashName()}"
        );
        Storage::assertExists(
            "{$response->json()['id']}/{$updatedFile->hashName()}"
        );
        Storage::assertExists(
            "{$response->json()['id']}/{$updatedThumb->hashName()}"
        );
    }
}
