<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessControlController extends Controller
{

    public function index(){
        return view('admins/access-control');
    }
    public function viewUsers(){
        $currentUser = request()->user();
        $users = User::all();
        $header = ['ID', 'Name', 'email', 'Date Created'];
        $rows = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'Date Created' => Carbon::parse($user->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'users' => $users, 
            'currentUser' => $currentUser,
            'header' => $header,
            'rows' => $rows,
            'url' => 'users'
        ];

        return view('admins/load-table', $data);
    }

    public function showUser($id){
        $user = User::findOrFail($id);
        $userPermissions = $user->permissions;
        $userRoles = $user->getRoleNames();
        $permissions = Permission::all()->diff($userPermissions);

        $data = [
            'user' => $user,
            'permissions' => $permissions,
            'user_roles' => $userRoles,
            'user_permissions' => $userPermissions
        ];

        return view('users/show', $data);
    }

    public function viewRoles(){
        $roles = Role::all();
        $header = ['ID', 'Name', 'Date Created'];
        $rows = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'Date Created' => Carbon::parse($role->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'roles' => $roles, 
            'header' => $header,
            'rows' => $rows,
            'url' => 'roles'
        ];

        return view('admins/load-table', $data);
    }

    public function showRole($id){
        $role = Role::findOrFail($id);
        $rolePermissions = $role->permissions;
        $permissions = Permission::all()->diff($rolePermissions);

        $data = [
            'role' => $role,
            'permissions' => $permissions,
            'role_permissions' => $rolePermissions
        ];

        return view('roles/show', $data);
    }

    public function viewPermissions(){
        $permissions = Permission::all();
        $header = ['ID', 'Name', 'Date Created'];
        $rows = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'Date Created' => Carbon::parse($permission->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'permissions' => $permissions, 
            'header' => $header,
            'rows' => $rows,
            'url' => 'permissions'
        ];

        return view('admins/load-table', $data);
    }

    public function createPermission(){
        return view('permissions/create');
    }

    public function storePermission(){
        Permission::create(['name' => request('name')]);

        return redirect('/admins/load-permissions');
    }

    public function showRoleCheckbox(){
        $search = request()->input('search');
        $roles = Role::where('name', 'like',  ['%' . strtolower($search) . '%'])->pluck('name', 'id');
        
        return view('/roles/checkbox', ['roles' => $roles]);
    }
}
