<?php

namespace App\Traits;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasUploads
{
    public function uploads(): MorphMany
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    public function upload(string $collection): MorphOne
    {
        return $this->morphOne(Upload::class, 'uploadable')
            ->where('collection', $collection)
            ->latestOfMany();
    }

    public function uploadMany(string $collection): MorphMany
    {
        return $this->morphMany(Upload::class, 'uploadable')
            ->where('collection', $collection);
    }
}
