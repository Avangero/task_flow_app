<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::whereIn('slug', ['admin', 'manager', 'user'])->get()->keyBy('slug');
        $hashedPassword = Hash::make('password');
        $now = now();

        $allUsers = [];

        $allUsers[] = [
            'first_name' => 'Александр',
            'last_name' => 'Администраторов',
            'email' => 'admin@taskflow.com',
            'password' => $hashedPassword,
            'phone' => '+7-900-123-45-67',
            'role_id' => $roles['admin']->id,
            'status' => UserStatus::ACTIVE,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $managerNames = [
            ['Мария', 'Петрова', 'maria.petrova@taskflow.com', '+7-900-234-56-78', UserStatus::ACTIVE],
            ['Дмитрий', 'Сидоров', 'dmitry.sidorov@taskflow.com', '+7-900-345-67-89', UserStatus::ACTIVE],
            ['Елена', 'Козлова', 'elena.kozlova@taskflow.com', '+7-900-456-78-90', UserStatus::INACTIVE],
        ];

        foreach ($managerNames as [$firstName, $lastName, $email, $phone, $status]) {
            $allUsers[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $hashedPassword,
                'phone' => $phone,
                'role_id' => $roles['manager']->id,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $userNames = [
            ['Анна', 'Иванова', 'anna.ivanova@taskflow.com', '+7-900-567-89-01', UserStatus::ACTIVE],
            ['Сергей', 'Волков', 'sergey.volkov@taskflow.com', '+7-900-678-90-12', UserStatus::ACTIVE],
            ['Ольга', 'Смирнова', 'olga.smirnova@taskflow.com', '+7-900-789-01-23', UserStatus::ACTIVE],
            ['Михаил', 'Новиков', 'mikhail.novikov@taskflow.com', '+7-900-890-12-34', UserStatus::BLOCKED],
            ['Татьяна', 'Федорова', 'tatyana.fedorova@taskflow.com', '+7-900-901-23-45', UserStatus::ACTIVE],
            ['Алексей', 'Морозов', 'alexey.morozov@taskflow.com', '+7-900-012-34-56', UserStatus::INACTIVE],
            ['Наталья', 'Кузнецова', 'natalya.kuznetsova@taskflow.com', '+7-900-123-45-67', UserStatus::ACTIVE],
            ['Владимир', 'Лебедев', 'vladimir.lebedev@taskflow.com', '+7-900-234-56-78', UserStatus::ACTIVE],
            ['Екатерина', 'Соколова', 'ekaterina.sokolova@taskflow.com', '+7-900-345-67-89', UserStatus::ACTIVE],
            ['Игорь', 'Попов', 'igor.popov@taskflow.com', '+7-900-456-78-90', UserStatus::BLOCKED],
        ];

        foreach ($userNames as [$firstName, $lastName, $email, $phone, $status]) {
            $allUsers[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $hashedPassword,
                'phone' => $phone,
                'role_id' => $roles['user']->id,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        User::insert($allUsers);
    }
}
