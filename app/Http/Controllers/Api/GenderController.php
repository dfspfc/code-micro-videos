<?php

namespace App\Http\Controllers\Api;

use App\Models\Gender;
use App\Http\Resources\Gender as GenderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenderController extends BaseApiController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean ',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->storeRules());

        $gender = DB::transaction(function () use ($request, $validatedData) {
            $gender = $this->model()::create($validatedData);
            $gender->categories()->sync($request->get('categories_id'));
            return $gender;   
        });
        $gender->refresh();
        
        $resource = $this->resource();
        return new $resource($gender);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->updateRules());
        $gender = $this->findObjectFromModel($id);

        return DB::transaction(function () use ($request, $validatedData, $gender) {
            $gender->update($validatedData);
            $gender->categories()->sync($request->get('categories_id'));

            $resource = $this->resource();
            return new $resource($gender);
        });
    }

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

    protected function resource()
    {
        return GenderResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }
}
