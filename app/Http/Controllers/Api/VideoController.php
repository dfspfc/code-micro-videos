<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'video_file' => 'nullable|mimes:mp4|max:500000',
            'thumb_file' => 'image|mimes:jpg|max:500000',
        ];
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->storeRules());
        $video = $this->model()::create($validatedData);
        $video->refresh();
        
        return $video;
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->updateRules());
        $video = $this->findObjectFromModel($id);
        $video->update($validatedData);
        
        return $video;
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
}
