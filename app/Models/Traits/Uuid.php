<?php

namespace App\Models\Traits;
use \Ramsey\Uuid\Uuid as RamseyUuid;

trait Uuid
{
    public static function boot()
    {
        parent::boot();
        static::creating(function($obj) {
            $obj->id = RamseyUuid::uuid4()->toString();
        });
    }

    public static function isValid($uuid)  {
        return RamseyUuid::isValid($uuid);
    }

    public static function newVersion4()
    {
        return RamseyUuid::uuid4()->toString();
    }
}