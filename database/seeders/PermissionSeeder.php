<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //event permissions
        Permission::query()->updateOrCreate([
            'name' => 'create-event',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'update-event',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'read-event',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'delete-event',
            'guard_name' => 'sanctum',
        ]);
        //member permissions
        Permission::query()->updateOrCreate([
            'name' => 'create-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'update-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'read-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'delete-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'import-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'export-member',
            'guard_name' => 'sanctum',
        ]);
        //telegram mailing permission
        Permission::query()->updateOrCreate([
            'name' => 'can-mailing',
            'guard_name' => 'sanctum',
        ]);
        //template permissions
        Permission::query()->updateOrCreate([
            'name' => 'create-template',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'update-template',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'read-template',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'delete-template',
            'guard_name' => 'sanctum',
        ]);

        //promocode permissions
        Permission::query()->updateOrCreate([
            'name' => 'create-promocode',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'update-promocode',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'read-promocode',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'delete-promocode',
            'guard_name' => 'sanctum',
        ]);

        //user permissions
        Permission::query()->updateOrCreate([
            'name' => 'create-user',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'update-user',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'read-user',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'delete-user',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'set-speaker',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'delete-speaker',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'upload-speaker',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'upload-event-program',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'export-event-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'import-event-member-report',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'upload-event-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'edit-event-member-fields',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'delete-event-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'add-event-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'get-event-report',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'update-event',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'can-activate-event-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'export-member',
            'guard_name' => 'sanctum',
        ]);
        Permission::query()->updateOrCreate([
            'name' => 'settings',
            'guard_name' => 'sanctum',
        ]);

        $superAdminRole = Role::query()->where('name', 'super-admin')->first();
        $superAdminRole->givePermissionTo([
            'create-event',
            'update-event',
            'read-event',
            'delete-event',
            'create-member',
            'update-member',
            'read-member',
            'delete-member',
            'import-member',
            'export-member',
            'can-mailing',
            'create-template',
            'update-template',
            'read-template',
            'delete-template',
            'create-promocode',
            'update-promocode',
            'read-promocode',
            'delete-promocode',
            'create-user',
            'update-user',
            'read-user',
            'delete-user',
            'set-speaker',
            'delete-speaker',
            'upload-speaker',
            'upload-event-program',
            'export-event-member',
            'import-event-member-report',
            'upload-event-member',
            'edit-event-member-fields',
            'delete-event-member',
            'add-event-member',
            'get-event-report',
            'update-event',
            'can-activate-event-member',
            'export-member',
            'settings'
        ]);

        $adminRole = Role::query()->where('name', 'admin')->first();
        $adminRole->givePermissionTo([
            'create-event',
            'update-event',
            'read-event',
            'create-member',
            'update-member',
            'read-member',
            'import-member',
            'export-member',
            'can-mailing',
            'create-template',
            'update-template',
            'read-template',
            'delete-template',
            'create-promocode',
            'update-promocode',
            'read-promocode',
            'create-user',
            'update-user',
            'read-user',
            'set-speaker',
            'delete-speaker',
            'upload-speaker',
            'upload-event-program',
            'export-event-member',
            'import-event-member-report',
            'upload-event-member',
            'edit-event-member-fields',
            'add-event-member',
            'get-event-report',
            'update-event',
            'can-activate-event-member',
            'export-member',
        ]);
        $manager = Role::query()->where('name', 'manager')->first();
        $manager->givePermissionTo([
            'read-event',
            'can-mailing',
            'set-speaker',
            'delete-speaker',
            'upload-event-program',
            'create-template',
            'update-template',
            'read-template',
            'delete-template',
        ]);
    }
}
