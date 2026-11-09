<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@ecommerce.com',
            'password' => 'password',
        ]);

        $admin->email_verified_at = now();

        $admin->assignRole(RoleEnum::ADMIN->value);
    }
}
