<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Алексей',
                'surname' => 'Волков',
                'email' => 'doc@starget.kz',
                'password' => Hash::make('password'),
                'role' => 'doc_manager',
            ],
            [
                'name' => 'Алексей',
                'surname' => 'Менеджер',
                'email' => 'client@starget.kz',
                'password' => Hash::make('password'),
                'role' => 'client_manager',
            ],
            [
                'name' => 'Александр',
                'surname' => 'В.',
                'email' => 'logist@starget.kz',
                'password' => Hash::make('password'),
                'role' => 'logistic_manager',
            ],
            [
                'name' => 'Admin',
                'surname' => 'Starget',
                'email' => 'admin@starget.kz',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $user->syncRoles([\Spatie\Permission\Models\Role::findByName($role, 'sanctum')]);
        }
    }
}
