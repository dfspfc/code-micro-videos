<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BaseApiController
{
    private $rules = ['name' => 'required|max:255'];
    
    protected function model()
    {
       return CategoryStub::class;
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