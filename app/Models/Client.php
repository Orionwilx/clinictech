<?php

namespace App\Models;

use App\Traits\HasUploads;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, HasUploads, SoftDeletes;

    protected $fillable = [
        'name',
        'nit',
        'email',
        'city',
        'country',
        'whatsapp',
        'phone',
        'is_active',
        'access_password',
        'user_id',
    ];

    protected $hidden = ['access_password'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'access_password' => 'encrypted',
        ];
    }

    public function logo(): MorphOne
    {
        return $this->upload('logo');
    }

    public function documents(): MorphMany
    {
        return $this->uploadMany('document');
    }

    /** Capacitaciones (Programa de Educación Continua) en PDF. */
    public function pec(): MorphMany
    {
        return $this->uploadMany('pec');
    }

    /** URL autenticada del logo (via MediaController). */
    public function logoUrl(): ?string
    {
        return $this->logo ? $this->logo->url() : null;
    }

    /** Logo como data-URI base64 para incrustar en PDFs (dompdf). */
    public function logoBase64(): ?string
    {
        $logo = $this->logo;
        if (! $logo) {
            return null;
        }

        $path = $logo->absolutePath();

        if (! file_exists($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return "data:{$mime};base64,".base64_encode((string) file_get_contents($path));
    }

    /** Cuenta de acceso (rol cliente) vinculada a esta empresa. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
