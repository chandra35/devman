<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuid, HasRoles, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'kemenag_username',
        'kemenag_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'kemenag_password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function setKemenagPasswordAttribute($value)
    {
        $this->attributes['kemenag_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDecryptedKemenagPassword(): ?string
    {
        return $this->kemenag_password ? Crypt::decryptString($this->kemenag_password) : null;
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }
}
