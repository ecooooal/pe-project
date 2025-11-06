<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Notification;

class NotificationController extends Controller
{
        public function index()
    {
        $user = Auth::user();
        
        $roleIds = $user->roles->pluck('id'); 
        
        //OLD CODE
        // $notifications = Notification::whereHas('roles', function ($query) use ($roleIds) {
        //     $query->whereIn('roles.id', $roleIds);
        // })->latest()->get();
        // dd($user->roles->pluck('id'), $notifications->toArray());

        //NEW CODE (TRIAL PHASE) HEREEEEEEEEEEEEE
        if ($roleIds->contains(7)) {
        // Super Admin can see everything
        $notifications = Notification::latest()->get();
        } 
        
        else {
        // Normal users - see only notifications linked to their roles OR public ones
        $notifications = Notification::where(function ($q) use ($roleIds) {
            $q->where('is_public', true)
              ->orWhereHas('roles', function ($query) use ($roleIds) {
                  $query->whereIn('roles.id', $roleIds);
              });
        })->latest()->get();
    }
    

    return view('notifications', data: [
        'notifications' => $notifications,
    ]);
        
    }

        public function studentIndex()
    {
        $user = Auth::user();
        $roleIds = $user->roles->pluck('id');

        $notifications = Notification::where(function ($q) use ($roleIds) {
            $q->where('is_public', true)
              ->orWhereHas('roles', function ($query) use ($roleIds) {
                  $query->whereIn('roles.id', $roleIds);
              });
        })->latest()->get();

    return view('students.student-notification', [
    'notifications' => $notifications,
]);
    }
}
