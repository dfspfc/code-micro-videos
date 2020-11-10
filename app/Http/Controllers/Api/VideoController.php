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
            'categories_id' => 'required|array|exists:categories,id',
            'genders_id' => 'required|array|exists:genders,id',
        ];
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->storeRules());

        $video = DB::transaction(function () use ($request, $validatedData) {
            $video = $this->model()::create($validatedData);
            $video->categories()->sync($request->get('categories_id'));
            $video->genders()->sync($request->get('genders_id'));
            return $video;   
        });
        $video->refresh();
        
        return $video;
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->updateRules());
        $video = $this->findObjectFromModel($id);

        return DB::transaction(function () use ($request, $validatedData, $video) {
            $video->update($validatedData);
            $video->categories()->sync($request->get('categories_id'));
            $video->genders()->sync($request->get('genders_id'));
            return $video;
        });
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
