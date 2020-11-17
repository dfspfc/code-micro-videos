<?php

namespace Tests\Prod\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait TestStorage
{
    protected function deleteAllFiles()
    {
        $dirs = Storage::directories();
        foreach ($dirs as $dir) {
            $files = Storage::files($dir);
            Storage::delete($files);
            Storage::deleteDirectory($dir);
        }
    }
}