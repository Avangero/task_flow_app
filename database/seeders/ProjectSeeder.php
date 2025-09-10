<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::with('role')->get();
        $admin = $users->firstWhere('email', 'admin@taskflow.com');
        $managers = $users->filter(fn ($user) => $user->role->slug === 'manager')->values();

        $now = now();

        $projectsData = [
            [
                'name' => 'Система управления задачами',
                'description' => 'Разработка веб-приложения для управления проектами и задачами команды. Включает в себя создание API, фронтенд интерфейса и мобильного приложения.',
                'status' => ProjectStatus::ACTIVE,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Интернет-магазин электроники',
                'description' => 'Создание полнофункционального интернет-магазина с каталогом товаров, корзиной, системой оплаты и доставки.',
                'status' => ProjectStatus::ACTIVE,
                'created_by' => $managers->get(0)->id,
            ],
            [
                'name' => 'CRM система для клиентов',
                'description' => 'Разработка системы управления взаимоотношениями с клиентами для отдела продаж.',
                'status' => ProjectStatus::COMPLETED,
                'created_by' => $managers->get(1)->id,
            ],
            [
                'name' => 'Мобильное приложение для фитнеса',
                'description' => 'Создание мобильного приложения для отслеживания тренировок и питания.',
                'status' => ProjectStatus::ACTIVE,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Система аналитики данных',
                'description' => 'Разработка платформы для сбора и анализа больших данных с интерактивными дашбордами.',
                'status' => ProjectStatus::ARCHIVED,
                'created_by' => $managers->get(0)->id,
            ],
            [
                'name' => 'Портал для образования',
                'description' => 'Создание онлайн-платформы для дистанционного обучения с видеоуроками и тестированием.',
                'status' => ProjectStatus::ACTIVE,
                'created_by' => $managers->get(1)->id,
            ],
            [
                'name' => 'Система бронирования отелей',
                'description' => 'Разработка веб-сервиса для поиска и бронирования номеров в отелях по всему миру.',
                'status' => ProjectStatus::COMPLETED,
                'created_by' => $admin->id,
            ],
        ];

        $projects = array_map(function ($project) use ($now) {
            return array_merge($project, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $projectsData);

        Project::insert($projects);
    }
}
