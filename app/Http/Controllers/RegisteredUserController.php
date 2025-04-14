<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class RegisteredUserController extends Controller
{

    public function create(){
        $roles = Role::pluck('name', 'id');
        $permissions = Permission::pluck('name', 'id');

        $data = [
            'roles' => $roles,
            'permissions' => $permissions
        ];

        return view('users/create', $data);
    }

    public function store(){
        $user_roles = Role::findMany(request('roles'));
        $user_permissions = Permission::findMany(request('permissions'));
        $user_author = request()->user();
        
        $validator = Validator::make(request()->all(), [
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', Password::min(6), 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect('admins/users/create')
                ->withErrors($validator)
                ->withInput();
        }


        $data = $validator->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']), 
        ]);

        $user->syncPermissions($user_permissions);
        $user->syncRoles($user_roles);

        return redirect('/admins/load-users');
    }
}
