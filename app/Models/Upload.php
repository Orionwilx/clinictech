<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    protected $fillable = [
        'uploadable_type',
        'uploadable_id',
        'collection',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'label',
        'uploaded_by',
    ];

    public function uploadable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** URL autenticada, servida por MediaController. */
    public function url(): string
    {
        return route('media.serve', $this);
    }

    /** Ruta absoluta en disco local (para incrustar en PDFs con dompdf). */
    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function humanSize(): string
    {
        $kb = $this->size / 1024;
        if ($kb < 1024) {
            return round($kb, 1).' KB';
        }

        return round($kb / 1024, 1).' MB';
    }

    /** Borra el archivo físico y el registro. */
    public function purge(): void
    {
        Storage::disk($this->disk)->delete($this->path);
        $this->delete();
    }
}
