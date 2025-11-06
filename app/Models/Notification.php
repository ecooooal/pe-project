<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class Notification extends Model
{
        protected $fillable = ['title', 'message', 'type', 'data', 'read_at', 'is_public'];

        protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    // 🔔 ROLE-BASED NOTIFICATIONS (Faculty, Dean, etc.)
    public function roles()
    {
        // return $this->belongsToMany(Role::class, 'notification_role', 'notification_id', 'role_id');
        return $this->belongsToMany(Role::class, 'notification_role', 'notification_id', 'role_id')
            ->withTimestamps();
    }

    // 👤 USER-SPECIFIC NOTIFICATIONS (Students)
    public function users()
    {
        return $this->belongsToMany(User::class, 'notification_user', 'notification_id', 'user_id')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }
}
