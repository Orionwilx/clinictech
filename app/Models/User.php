<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUploads;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUploads, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function signature(): MorphOne
    {
        return $this->upload('signature');
    }

    public function signatureBase64(): ?string
    {
        $sig = $this->signature;
        if (! $sig) {
            return null;
        }

        $path = $sig->absolutePath();

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
     * Empresa cliente asociada a esta cuenta (si el usuario es un cliente).
     */
    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Ficha de técnico asociada a esta cuenta (si el usuario es un técnico).
     */
    public function technician(): HasOne
    {
        return $this->hasOne(Technician::class);
    }
}
