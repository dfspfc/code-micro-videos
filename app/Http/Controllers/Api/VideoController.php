<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Http\Request;
use App\Http\Resources\Video as VideoResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VideoController extends BaseApiController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genders_id' => 'required|array|exists:genders,id,deleted_at,NULL',
            'video_file' => 'mimes:mp4|max:50000000',
            'trailer_file' => 'mimes:mp4|max:1000000',
            'thumb_file' => 'image|mimes:jpg,jpeg|max:5000',
            'banner_file' => 'image|mimes:jpg,jpeg|max:10000',
        ];
    }

    public function index()
    {
        $videos = !$this->paginationSize ? 
            $this->model()::with('categories')->with('genders')->get() :
            $this->model()::with('categories')->with('genders')->paginate($this->paginationSize);

        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($this->resourceCollection());
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($videos)
            : $resourceCollectionClass::collection($videos);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->storeRules());
        $video = $this->model()::create($validatedData);
        $video->refresh();
        
        $resource = $this->resource();
        return new $resource($video);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->updateRules());
        $video = $this->findObjectFromModel($id);
        $video->update($validatedData);
        
        $resource = $this->resource();
        return new $resource($video);
    }

    protected function model()
    {
       return Video::class;
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
        return VideoResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }
}
