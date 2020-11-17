<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Uuid;
use App\Models\Traits\UploadFiles;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use SoftDeletes, Uuid, UploadFiles;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title', 
        'description',
        'year_launched', 
        'opened',
        'rating', 
        'duration',
        'video_file',
        'thumb_file',
    ];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer',
    ];
    public $incrementing = false;
    public static $fileFields = [
        'video_file',
        'thumb_file',
    ];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();
            $video = static::query()->create($attributes);
            static::handleRelations($video, $attributes);
            $video->UploadFiles($files);
            DB::commit();
            return $video;
        } catch (\Exception $e) {
            if (isset($video)) {
                $video->deleteFiles($files);
            }
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            
            if($saved) {
                $this->UploadFiles($files);
            }

            DB::commit();

            if($saved && count($files)) {
                $this->deleteOldFiles();
            }

            return $saved;
        } catch (\Exception $e) {
            $this->deleteFiles($files);
            DB::rollBack();
            throw $e;
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genders()
    {
        return $this->belongsToMany(Gender::class)->withTrashed();
    }

    private static function handleRelations(Video $video, array $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }
        if (isset($attributes['genders_id'])) {
            $video->genders()->sync($attributes['genders_id']);
        }
    }

    protected function uploadDir()
    {
        return $this->id;
    }
}


