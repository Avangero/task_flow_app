<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            [
                'slug' => 'admin',
                'name' => 'Администратор',
                'permissions' => json_encode([
                    'users.create',
                    'users.read',
                    'users.update',
                    'users.delete',
                    'projects.create',
                    'projects.read',
                    'projects.update',
                    'projects.delete',
                    'tasks.create',
                    'tasks.read',
                    'tasks.update',
                    'tasks.delete',
                    'roles.manage',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'manager',
                'name' => 'Менеджер',
                'permissions' => json_encode([
                    'users.read',
                    'projects.create',
                    'projects.read',
                    'projects.update',
                    'tasks.create',
                    'tasks.read',
                    'tasks.update',
                    'tasks.delete',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'user',
                'name' => 'Пользователь',
                'permissions' => json_encode([
                    'projects.read',
                    'tasks.read',
                    'tasks.update',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        Role::insert($roles);
    }
}
