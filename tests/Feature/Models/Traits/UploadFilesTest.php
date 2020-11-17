<?php

namespace Tests\Feature\Models\Traits;

use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    private $uploadFile;

    protected function setUp() : void {
        parent::setUp();
        $this->uploadFile = new UploadFilesStub();

        UploadFilesStub::dropTable();
        UploadFilesStub::makeTable();
    }

    public function testMakeOldFilesOnSaving()
    {
        $this->uploadFile->fill([
            'name' => 'test',
            'file1' => 'test1.mp4',
            'file2' => 'test2.mp4',
        ]);

        $this->uploadFile->save();

        $this->assertCount(0, $this->uploadFile->oldFiles);

        $this->uploadFile->update([
            'name' => 'new name',
            'file2' => 'test3.mp4',
        ]);

        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->uploadFile->oldFiles);
    }

    public function testMakeOldFilesNullOnSaving()
    {
        $this->uploadFile->fill([
            'name' => 'test',
        ]);

        $this->uploadFile->save();

        $this->uploadFile->update([
            'name' => 'new name',
            'file2' => 'test3.mp4',
        ]);

        $this->assertEqualsCanonicalizing([], $this->uploadFile->oldFiles);
    }
}
