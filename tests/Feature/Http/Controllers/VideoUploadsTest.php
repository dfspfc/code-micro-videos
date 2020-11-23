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
        $videoFile = UploadedFile::fake()->create('video.mp4')->size('50000001');
        $trailer = UploadedFile::fake()->create('video.mp4')->size('1000001');
        $thumb = UploadedFile::fake()->image('thumb.jpg')->size('5001');
        $banner = UploadedFile::fake()->image('thumb.jpg')->size('10001');

        $response = $this->postVideo([
            'video_file' => $videoFile,
            'trailer_file' => $trailer,
            'thumb_file' => $thumb,
            'banner_file' => $banner,
        ]);

        $response->assertStatus(422);
        $this->assertMaxFileSize($response, 'video_file', 50000000);
        $this->assertMaxFileSize($response, 'trailer_file', 1000000);
        $this->assertMaxFileSize($response, 'thumb_file', 5000);
        $this->assertMaxFileSize($response, 'banner_file', 10000);
    }

    public function testCreatePassingFileTypesDifferentFromWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $videoFile = UploadedFile::fake()->create('video.mp3')->size('50000000');
        $trailer = UploadedFile::fake()->create('video.mp2')->size('1000000');
        $thumb = UploadedFile::fake()->image('thumb.test')->size('5000');
        $banner = UploadedFile::fake()->image('thumb.png')->size('10000');

        $response = $this->postVideo([
            'video_file' => $videoFile,
            'trailer_file' => $trailer,
            'thumb_file' => $thumb,
            'banner_file' => $banner,
        ]);

        $response->assertStatus(422);
        $this->assertFileType($response, 'video_file', 'mp4');
        $this->assertFileType($response, 'trailer_file', 'mp4');
        $this->assertFileType($response, 'thumb_file', 'jpg, jpeg');
        $this->assertFileType($response, 'banner_file', 'jpg, jpeg');
    }

    public function testUpdatePassingFileAttributesLargerThanWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $videoFile = UploadedFile::fake()->create('video.mp4')->size('50000001');
        $trailer = UploadedFile::fake()->create('video.mp4')->size('1000001');
        $thumb = UploadedFile::fake()->image('thumb.jpg')->size('5001');
        $banner = UploadedFile::fake()->image('thumb.jpg')->size('10001');

        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'video_file' => $videoFile,
                'trailer_file' => $trailer,
                'thumb_file' => $thumb,
                'banner_file' => $banner,
            ]
        );

        $response->assertStatus(422);
        $this->assertMaxFileSize($response, 'video_file', 50000000);
        $this->assertMaxFileSize($response, 'trailer_file', 1000000);
        $this->assertMaxFileSize($response, 'thumb_file', 5000);
        $this->assertMaxFileSize($response, 'banner_file', 10000);
    }

    public function testUpdatePassingFileTypesDifferentFromWhatIsAllowedShouldReturn422()
    {
        Storage::fake();
        $videoFile = UploadedFile::fake()->create('video.mp3')->size('50000000');
        $trailer = UploadedFile::fake()->create('video.mp2')->size('1000000');
        $thumb = UploadedFile::fake()->image('thumb.test')->size('5000');
        $banner = UploadedFile::fake()->image('thumb.png')->size('10000');

        $video = factory(Video::class)->create();
        $response = $this->updateVideo(
            $video->id, 
            [
                'video_file' => $videoFile,
                'trailer_file' => $trailer,
                'thumb_file' => $thumb,
                'banner_file' => $banner,
            ]
        );

        $response->assertStatus(422);
        $this->assertFileType($response, 'video_file', 'mp4');
        $this->assertFileType($response, 'trailer_file', 'mp4');
        $this->assertFileType($response, 'thumb_file', 'jpg, jpeg');
        $this->assertFileType($response, 'banner_file', 'jpg, jpeg');
    }

    public function testCreatePassingAllFileFieldsShouldCreateAndUploadAllFilesReturn201()
    {
        Storage::fake();
        $videoFile = UploadedFile::fake()->create('video.mp4')->size('50000000');
        $trailer = UploadedFile::fake()->create('video.mp4')->size('1000000');
        $thumb = UploadedFile::fake()->image('thumb.jpg')->size('5000');
        $banner = UploadedFile::fake()->image('thumb.jpg')->size('10000');
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
            'video_file' => $videoFile,
            'trailer_file' => $trailer,
            'thumb_file' => $thumb,
            'banner_file' => $banner,
        ];
        $response = $this->postVideo($requestBody);

        $response
            ->assertStatus(201)
            ->assertJsonFragment(['video_file' => $videoFile->hashName()]);
        $response
            ->assertStatus(201)
            ->assertJsonFragment(['trailer_file' => $trailer->hashName()]);
        $response
            ->assertStatus(201)
            ->assertJsonFragment(['thumb_file' => $thumb->hashName()]);
        $response
            ->assertStatus(201)
            ->assertJsonFragment(['banner_file' => $banner->hashName()]);
        Storage::assertExists(
            "{$response->json('data.id')}/{$videoFile->hashName()}"
        );
        Storage::assertExists(
            "{$response->json('data.id')}/{$trailer->hashName()}"
        );
        Storage::assertExists(
            "{$response->json('data.id')}/{$thumb->hashName()}"
        );
        Storage::assertExists(
            "{$response->json('data.id')}/{$banner->hashName()}"
        );
    }

    public function testUpdateShouldUpdateThenUploadTheNewFilesThenDeleteOldFilesAndReturn200()
    {
        Storage::fake();
        $formerRelatedVideoFile = UploadedFile::fake()->create('video.mp4')->size('50000000');
        $formerRelatedTrailer = UploadedFile::fake()->create('video.mp4')->size('1000000');
        $formerRelatedThumb = UploadedFile::fake()->image('thumb.jpg')->size('5000');
        $formerRelatedBanner = UploadedFile::fake()->image('thumb.jpg')->size('10000');
        $video = factory(Video::class)->create([
            'title' => 'title test to be updated',
            'description' => 'description test to be updated',
            'year_launched' => 2008,
            'opened' => true,
            'rating' => 'L',
            'duration' => 20,
            'video_file' => $formerRelatedVideoFile,
            'trailer_file' => $formerRelatedTrailer,
            'thumb_file' => $formerRelatedThumb,
            'banner_file' => $formerRelatedBanner,
        ]);

        $updatedRelatedCategory = factory(Category::class)->create(['name' => 'related category']);
        $updatedRelatedGender = factory(Gender::class)->create(['name' => 'related gender']);
        $updatedVideoFile = UploadedFile::fake()->create('video.mp4')->size('50000000');
        $updatedTrailer = UploadedFile::fake()->create('video.mp4')->size('1000000');
        $updatedThumb = UploadedFile::fake()->image('thumb.jpg')->size('5000');
        $updatedBanner = UploadedFile::fake()->image('thumb.jpg')->size('10000');
        $updateRequestBody = [
            'title' => 'updated title test',
            'description' => 'updated description test',
            'year_launched' => 2009,
            'opened' => false,
            'rating' => '10',
            'duration' => 29,
            'categories_id' => [$updatedRelatedCategory->id],
            'genders_id' => [$updatedRelatedGender->id],
            'video_file' => $updatedVideoFile,
            'trailer_file' => $updatedTrailer,
            'thumb_file' => $updatedThumb,
            'banner_file' => $updatedBanner,
        ];
        $response = $this->updateVideo($video->id, $updateRequestBody);
        
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'video_file' => $updatedVideoFile->hashName(),
                'trailer_file' => $updatedTrailer->hashName(),
                'thumb_file' => $updatedThumb->hashName(),
                'banner_file' => $updatedBanner->hashName(),
            ]);
        $this->assertDatabaseMissing(
            'videos',
            [
                'video_file' => $formerRelatedVideoFile->hashName(),
                'trailer_file' => $formerRelatedTrailer->hashName(),
                'thumb_file' => $formerRelatedThumb->hashName(),
                'banner_file' => $formerRelatedBanner->hashName(),
            ]
        );
        $this->assertDatabaseHas(
            'videos',
            [
                'video_file' => $updatedVideoFile->hashName(),
                'trailer_file' => $updatedTrailer->hashName(),
                'thumb_file' => $updatedThumb->hashName(),
                'banner_file' => $updatedBanner->hashName(),
            ]
        );
        Storage::assertMissing(
            "{$response->json('data.id')}/{$formerRelatedVideoFile->hashName()}"
        );
        Storage::assertMissing(
            "{$response->json('data.id')}/{$formerRelatedTrailer->hashName()}"
        );
        Storage::assertMissing(
            "{$response->json('data.id')}/{$formerRelatedThumb->hashName()}"
        );
        Storage::assertMissing(
            "{$response->json('data.id')}/{$formerRelatedBanner->hashName()}"
        );
        Storage::assertExists(
            "{$response->json('data.id')}/{$updatedVideoFile->hashName()}"
        );
        Storage::assertExists(
            "{$response->json('data.id')}/{$updatedTrailer->hashName()}"
        );
        Storage::assertExists(
            "{$response->json('data.id')}/{$updatedThumb->hashName()}"
        );
        Storage::assertExists(
            "{$response->json('data.id')}/{$updatedBanner->hashName()}"
        );
    }
}
