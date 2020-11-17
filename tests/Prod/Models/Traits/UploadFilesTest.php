<?php

namespace Tests\Prod\Models\Traits;

use Tests\Stubs\Models\UploadFilesStub;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class UploadFilesTest extends TestCase
{
    use TestStorage, TestProd;

    private $uploadFile;

    protected function setUp() : void {
        parent::setUp();
        $this->skipTestIfNotProd();
        $this->uploadFile = new UploadFilesStub();
        Config::set('filesystems.default', 'gcs');
        $this->deleteAllFiles();
    }

    public function testUploadFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadFile->uploadFile($file);

        Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadFile->uploadFiles([$file1, $file2]);

        Storage::assertExists("1/{$file1->hashName()}");
        Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadFile->uploadFile($file);
        $this->uploadFile->deleteFile($file);

        Storage::assertMissing("1/{$file->hashName()}");
    }

    public function testDeleteFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadFile->uploadFiles([$file1, $file2]);
        $this->uploadFile->deleteFiles([$file1->hashName(), $file2]);

        Storage::assertMissing("1/{$file1->hashName()}");
        Storage::assertMissing("1/{$file2->hashName()}");
    }
}
