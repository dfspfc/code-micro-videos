<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UploadFiles;

class UploadFilesStub extends Model
{
    use UploadFiles;

    public static $fileFields = [
        'file1',
        'file2',
    ];

    protected $fillable = [
        'name', 
        'file1',
        'file2',
    ];

    public static function makeTable()
    {
        Schema::create('upload_files_stubs', function ($table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('file1')->nullable();
            $table->string('file2')->nullable();
            $table->timeStamps();
        });
    }

    public static function dropTable()
    {
        Schema::dropIfExists('upload_files_stubs');
    }

    protected function uploadDir()
    {
        return '1';
    }
}