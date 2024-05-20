<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class PermissionRoleSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'create users',
            'edit users',
            'delete users',
            'create roles',
            'edit roles',
            'delete roles',
            'create sections',
            'edit sections',
            'delete sections',
            // Add more permissions as needed
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $roles = [
            'admin',
            'super admin',
            'user',
            // Add more roles as needed
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
        $permissions = Permission::pluck('id', 'id')->all();
        $admin_role = Role::where('name','super admin')->first();
        $admin_role->syncPermissions($permissions);
    }
}