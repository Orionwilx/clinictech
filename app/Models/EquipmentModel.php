<?php

namespace App\Models;

use Database\Factories\EquipmentModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentModel extends Model
{
    /** @use HasFactory<EquipmentModelFactory> */
    use HasFactory;

    protected $fillable = ['brand_id', 'category_id', 'name'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'model_id');
    }
}
