<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'username' => 'regularUser',
            'email' => 'regularuser@ccrproject.dev',
            'name' => 'Regular User',
            'password' => Hash::make('regularPassword!@#'),
        ]);

        $user = User::factory()->create([
            'username' => 'systemAdminUser',
            'email' => 'adminuser@ccrproject.dev',
            'name' => 'System Admin User',
            'password' => Hash::make('adminPassword!@#'),
        ]);
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
    }
}
