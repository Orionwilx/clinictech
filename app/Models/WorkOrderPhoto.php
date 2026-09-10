<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WorkOrderPhoto extends Model
{
    protected $fillable = ['work_order_id', 'path', 'original_name', 'size'];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Ruta absoluta en disco (para incrustar en PDF con dompdf).
     */
    public function absolutePath(): string
    {
        return Storage::disk('public')->path($this->path);
    }
}
