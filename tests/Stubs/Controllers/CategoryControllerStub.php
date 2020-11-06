<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BaseApiController
{
    protected function model()
    {
       return CategoryStub::class;
    }

    protected function storeRules()
    {
        return ['name' => 'required|max:255'];
    }
}