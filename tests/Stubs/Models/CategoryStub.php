<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryStub extends Model
{
    const TABLE = 'category_stubs';
    protected $table = self::TABLE;
    protected $fillable = ['name', 'description'];

    public static function createTable()
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        Schema::dropIfExists(self::TABLE);
    }
}