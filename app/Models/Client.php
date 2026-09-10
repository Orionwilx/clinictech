<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'logo_path',
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
            // Copia reversible de la contraseña de acceso, solo para consulta del admin.
            'access_password' => 'encrypted',
        ];
    }

    /**
     * URL pública del logo (o null si no tiene).
     */
    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    /**
     * Logo como data-URI base64 para incrustar en PDFs (dompdf).
     */
    public function logoBase64(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        $path = storage_path('app/public/'.$this->logo_path);

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

    /**
     * Cuenta de acceso (rol cliente) vinculada a esta empresa.
     */
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
