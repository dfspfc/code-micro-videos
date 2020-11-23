<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseApiController extends Controller
{
    protected $paginationSize = 15;

    protected abstract function model();
    protected abstract function storeRules();
    protected abstract function updateRules();
    protected abstract function resource();
    protected abstract function resourceCollection();


    public function index()
    {
        $data = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);

        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($this->resourceCollection());
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data);
    }
    
    public function show($id)
    {
        $obj =  $this->findObjectFromModel($id);
        $resource = $this->resource();
        return new $resource($obj);

    }
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->storeRules());
        $obj =  $this->model()::create($validatedData);
        $obj->refresh();
        
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {

        $validatedData = $this->validate($request, $this->updateRules());
        $obj = $this->findObjectFromModel($id);
        $obj->update($validatedData);
        
        $resource = $this->resource();
        return new $resource($obj);
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
