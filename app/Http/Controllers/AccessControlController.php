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

    public function createRole(){
        $permissions = Permission::pluck('name', 'id');

        $data = [
            'permissions' => $permissions
        ];

        return view('roles/create', $data);
    }

    public function storeRole(){
        $role_permissions = Permission::findMany(request('permissions'));
        $role = Role::create(['name' => request('name')]);

        $role->syncPermissions($role_permissions);

        return redirect('/admins/load-roles');
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

    public function loadRoleCheckbox(){
        // \Log::info('HTMX payload:', request()->all());
        $user_roles = request()->input('user_id') 
        ? User::findOrFail(request()->input('user_id'))->getRoleNames()
        : null;
    
        $search = request()->input('search');
        $roles = Role::where('name', 'like',  ['%' . strtolower($search) . '%'])->pluck('name', 'id');
        
        $data = [
            'roles' => $roles,
            'user_roles' => $user_roles
        ];

        return view('/roles/checkbox', $data);
    }
}
