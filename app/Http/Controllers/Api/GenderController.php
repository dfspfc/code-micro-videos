<?php

namespace App\Http\Controllers\Api;

use App\Models\Gender;   

class GenderController extends BaseApiController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean ',
    ];

    protected function model()
    {
       return Gender::class;
    }

    protected function storeRules()
    {
        return $this->rules;
    }

    protected function updateRules()
    {
        return $this->rules;
    }
}
