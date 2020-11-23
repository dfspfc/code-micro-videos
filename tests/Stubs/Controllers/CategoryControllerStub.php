<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use Tests\Stubs\Models\CategoryStub;
use App\Http\Resources\Category as CategoryResource;

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

    protected function resource()
    {
        return CategoryResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }
}