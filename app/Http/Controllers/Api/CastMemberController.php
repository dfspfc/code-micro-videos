<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;
use App\Http\Resources\CastMember as CastMemberResource;

class CastMemberController extends BaseApiController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'name' => 'required|max:255',
            'type' => 'required|in:' . implode(',', [CastMember::TYPE_ACTOR, CastMember::TYPE_DIRECTOR]),
        ];
    }

    protected function model()
    {
       return CastMember::class;
    }

    protected function storeRules()
    {
        return $this->rules;
    }

    protected function updateRules()
    {
        return $this->rules;
    }

    protected function resource()
    {
        return CastMemberResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }
}
