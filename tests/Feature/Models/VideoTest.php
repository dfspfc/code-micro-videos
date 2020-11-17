<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use App\Models\Gender;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Exceptions\TestTransactionException;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\TransactionCommitted;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreateIfTransactionFailsShouldRollbackFilesThatWereUploaded()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.jpeg')->size('500000');
        $thumb = UploadedFile::fake()->create('thumb.png')->size('500000');
        $category = factory(Category::class)->create(['name' => 'category']);
        $gender = factory(Gender::class)->create(['name' => 'gender']);

        Event::listen(TransactionCommitted::class, function() {
            throw new TestTransactionException();
        });

        $hasError = false;

        try{
            Video::create([
                'title' => 'title test to be updated',
                'description' => 'description test to be updated',
                'year_launched' => 2008,
                'opened' => true,
                'rating' => 'L',
                'duration' => 20,
                'categories_id' => [$category->id],
                'genders_id' => [$gender->id],
                'video_file' => $file,
                'thumb_file' => $thumb,
            ]);
        } catch (TestTransactionException $e) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testUpdateIfTransactionFailsShouldRollbackFilesThatWereUploaded()
    {
        Storage::fake();

        $formerRelatedFile = UploadedFile::fake()->create('former_video.mp4')->size('1000');
        $formerRelatedThumb = UploadedFile::fake()->image('thumb.jpg')->size('1000');
        $videoOnDB = factory(Video::class)->create([
            'title' => 'title test to be updated',
            'description' => 'description test to be updated',
            'year_launched' => 2008,
            'opened' => true,
            'rating' => 'L',
            'duration' => 20,
            'video_file' => $formerRelatedFile,
            'thumb_file' => $formerRelatedThumb,
        ]);

        $fileToUpdate = UploadedFile::fake()->create('former_video.mp4')->size('1000');
        $thumbToUpdate = UploadedFile::fake()->image('thumb.jpg')->size('1000');
        $category = factory(Category::class)->create(['name' => 'category']);
        $gender = factory(Gender::class)->create(['name' => 'gender']);
        Event::listen(TransactionCommitted::class, function() {
            throw new TestTransactionException();
        });

        $hasError = false;

        try{
            $videoOnDB->update([
                'title' => 'new title',
                'description' => 'new description',
                'year_launched' => 2020,
                'opened' => false,
                'rating' => '12',
                'duration' => 21,
                'categories_id' => [$category->id],
                'genders_id' => [$gender->id],
                'video_file' => $fileToUpdate,
                'thumb_file' => $thumbToUpdate,
            ]);
        } catch (TestTransactionException $e) {
            $this->assertCount(2, Storage::allFiles());
            Storage::assertExists(
                "{$videoOnDB->id}/{$formerRelatedFile->hashName()}"
            );
            Storage::assertExists(
                "{$videoOnDB->id}/{$formerRelatedThumb->hashName()}"
            );
            Storage::assertMissing(
                "{$videoOnDB->id}/{$fileToUpdate->hashName()}"
            );
            Storage::assertMissing(
                "{$videoOnDB->id}/{$thumbToUpdate->hashName()}"
            );
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }
}
