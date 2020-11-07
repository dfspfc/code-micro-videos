<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Uuid;

class CastMember extends Model
{
    use SoftDeletes, Uuid;

    const TYPE_ACTOR = 1;
    const TYPE_DIRECTOR = 2;

    protected $fillable = ['name', 'type'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id' => 'string',
    ];
    public $incrementing = false;
}
