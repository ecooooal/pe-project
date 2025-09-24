<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class AccessControlController extends Controller
{

    public function indexUsers(){
        $currentUser = request()->user();
        $users = User::paginate(10);
        $header = ['ID', 'Name', 'email', 'Date Created'];
        $rows = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->getFullName(),
                'email' => $user->email,
                'Date Created' => Carbon::parse($user->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'models' => $users, 
            'currentUser' => $currentUser,
            'header' => $header,
            'rows' => $rows,
            'url' => 'users'
        ];

        return view('admins/access-control', $data);
    }

    public function redirect(){
        return redirect('/admins/users');
    }

    public function indexRoles(){
        $roles = Role::paginate(10);
        $header = ['ID', 'Name', 'Date Created'];
        $rows = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'Date Created' => Carbon::parse($role->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'models' => $roles, 
            'header' => $header,
            'rows' => $rows,
            'url' => 'roles'
        ];

        return view('admins/roles', $data);
    }

    public function showRole(Role $role){
        $rolePermissions = $role->permissions;
        $permissions = Permission::all()->diff($rolePermissions);
        $data = [
            'role' => $role,
            'permissions' => $permissions,
            'role_permissions' => $rolePermissions
        ];

        return view('/roles/show', $data);
    }

    public function createRole(){
        $permissions = Permission::pluck('name', 'id');

        $data = [
            'permissions' => $permissions
        ];

        return view('/roles/create', $data);
    }

    public function storeRole(){
        $role_permissions = Permission::findMany(request('permissions'));
        $role = Role::create(['name' => request('name')]);

        $role->syncPermissions($role_permissions);

        return redirect()->route('admin.roles.show', $role);
    }

    public function editRole(Role $role){
        $role_permissions = $role->permissions()->pluck('name', 'id');
        $permissions = Permission::all()->pluck('name', 'id')->diff($role_permissions);

        $data = [
            'role' => $role,
            'role_permissions' => $role_permissions,
            'permissions' => $permissions,
        ];

        return view('roles/edit', $data);
    }

    public function updateRole(Role $role) {
        $validator = Validator::make(request()->all(), [
            'name' => ['required'],
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $data = $validator->validated();
    
        $role->update([
            'name' => $data['name'],
        ]);

        $role_permissions = Permission::findMany(request('permissions'));

        $role->syncPermissions($role_permissions);

        return redirect()->route('admin.roles.show', $role);    
    }

    public function destroyRole(Role $role){
        // authorize

        $role->delete();

        return redirect()->route('admin.roles.index');
    }

    public function loadRoleCheckbox(){
        
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
