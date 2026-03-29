<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'username',
        'app_name',
        'ip_address',
        'user_agent',
        'device_info',
        'status',
        'notes',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
