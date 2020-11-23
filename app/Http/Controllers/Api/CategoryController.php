<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Resources\Category as CategoryResource;

class CategoryController extends BaseApiController
{
    private $rules = [
        'name' => 'required|max:255',
        'description' => 'nullable',
        'is_active' => 'boolean ',
    ];

    protected function model()
    {
       return Category::class;
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
