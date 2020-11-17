<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UploadFiles;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadFilesTest extends TestCase
{
    private $uploadFile;

    protected function setUp() : void {
        parent::setUp();
        $this->uploadFile = new UploadFilesStub();
    }

    public function testUploadFile()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadFile->uploadFile($file);

        Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadFile->uploadFiles([$file1, $file2]);

        Storage::assertExists("1/{$file1->hashName()}");
        Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteFile()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadFile->uploadFile($file);
        $this->uploadFile->deleteFile($file);

        Storage::assertMissing("1/{$file->hashName()}");
    }

    public function testDeleteFiles()
    {
        Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadFile->uploadFiles([$file1, $file2]);
        $this->uploadFile->deleteFiles([$file1->hashName(), $file2]);

        Storage::assertMissing("1/{$file1->hashName()}");
        Storage::assertMissing("1/{$file2->hashName()}");
    }

    public function testExtractFiles()
    {
        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        //
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $attributes = [
            'file1' => $file1,
            'test' => 'test'
        ];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(
            [
                'file1' => $file1->hashName(),
                'test' => 'test'
            ],
            $attributes
        );
        $this->assertCount(1, $files);
        $this->assertEquals([$file1], $files);

        //
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $file3 = UploadedFile::fake()->create('video3.mp4');
        $attributes = [
            'file1' => $file2,
            'file2' => $file3,
            'another test' => 1,
        ];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals(
            [
                'file1' => $file2->hashName(),
                'file2' => $file3->hashName(),
                'another test' => 1
            ],
            $attributes
        );
        $this->assertCount(2, $files);
        $this->assertEquals([$file2, $file3], $files);
    }
}
