<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::query()->updateOrCreate([
            'name' => 'admin',
            'guard_name' => 'sanctum'
        ]);
        Role::query()->updateOrCreate([
            'name' => 'manager',
            'guard_name' => 'sanctum'
        ]);
        Role::query()->updateOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'sanctum'
        ]);
        $user = User::where('email', 'yensepbay@gmail.com')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@admin.kz',
                'email_verified_at' => now(),
                'password' => bcrypt(123456789),
                'remember_token' => Str::random(10),
            ]);
        }
        $user->syncRoles('super-admin');
    }
}
