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

    public function show(User $user){
        $userPermissions = $user->permissions()->pluck('name');
        $userRoles = $user->getRoleNames();
        $permissions = Permission::all()->pluck('name')->diff($userPermissions);

        $data = [
            'user' => $user,
            'permissions' => $permissions,
            'user_roles' => $userRoles,
            'user_permissions' => $userPermissions
        ];

        return view('users/show', $data);
    }


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

    public function edit(User $user){
        $userPermissions = $user->permissions()->pluck('name', 'id');
        $roles = Role::pluck('name', 'id');
        $userRoles = $user->getRoleNames();
        $permissions = Permission::all()->pluck('name', 'id')->diff($userPermissions);

        $data = [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
            'user_roles' => $userRoles,
            'user_permissions' => $userPermissions
        ];

        return view('users/edit', $data);
    }

    public function update(User $user) {
        $user_roles = Role::findMany(request('roles'));
        $user_permissions = Permission::findMany(request('permissions'));

        $validator = Validator::make(request()->all(), [
            'name' => ['required'],
            'email' => ['required', 'email'],
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $data = $validator->validated();
    
        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];
    
        $user->update($updateData);
        $user->syncPermissions($user_permissions);
        $user->syncRoles($user_roles);

        return redirect()->route('admin.users.show', $user)
        ->with('success', 'User updated successfully.');    
    }

    public function destroy(User $user){

        $this->authorize('delete', $user);

        $user->delete();

        return redirect('/admins/access-control');

    }
}
