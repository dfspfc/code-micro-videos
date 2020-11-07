<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

abstract class BaseApiController extends Controller
{
    protected abstract function model();
    protected abstract function storeRules();
    protected abstract function updateRules();

    public function index()
    {
        return $this->model()::all();
    }
    
    public function show($id)
    {
        return $this->findObjectFromModel($id);
    }
    
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->storeRules());
        $obj =  $this->model()::create($validatedData);
        $obj->refresh();
        
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findObjectFromModel($id);
        $validatedData = $this->validate($request, $this->updateRules());
        $obj->update($validatedData);
        
        return $obj;

    }

    public function destroy($id)
    {
        $obj = $this->findObjectFromModel($id);
        $obj->delete();

        return response()->noContent();
    }

    protected function findObjectFromModel($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();

        return $this->model()::where($keyName, $id)->firstOrFail();
    }
}
