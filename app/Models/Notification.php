<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class Notification extends Model
{
        protected $fillable = ['title', 'message', 'type', 'data', 'read_at',];

        protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'notification_role', 'notification_id', 'role_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'notification')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }
}
