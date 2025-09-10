<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all()->keyBy('id');
        $users = User::with('role')->get();
        $managers = $users->filter(fn ($user) => $user->role->slug === 'manager');
        $regularUsers = $users->filter(fn ($user) => $user->role->slug === 'user');
        $admin = $users->firstWhere('email', 'admin@taskflow.com');

        $tasks = [];
        $now = Carbon::now();

        $taskTemplates = [
            'development' => [
                'Настройка базы данных' => 'Создать миграции для всех основных таблиц системы',
                'Разработка API аутентификации' => 'Реализовать JWT аутентификацию с регистрацией и логином',
                'Создание пользовательского интерфейса' => 'Разработать CRUD операции через веб-интерфейс',
                'Система уведомлений' => 'Добавить email и push уведомления для важных событий',
                'Оптимизация производительности' => 'Провести анализ и оптимизировать медленные запросы',
                'Тестирование функционала' => 'Написать unit и интеграционные тесты',
                'Документация' => 'Создать подробную документацию для API',
                'Код-ревью безопасности' => 'Провести аудит безопасности всех эндпоинтов',
            ],
            'ecommerce' => [
                'Каталог товаров' => 'Создать систему категорий и товаров с фильтрацией',
                'Интеграция платежей' => 'Подключить оплату через банковские карты',
                'Система заказов' => 'Разработать административную панель для управления заказами',
                'Мобильная адаптация' => 'Адаптировать интерфейс для мобильных устройств',
                'Система доставки' => 'Интегрировать с службами доставки',
                'Личный кабинет' => 'Создать профиль пользователя с историей заказов',
            ],
            'analytics' => [
                'Сбор данных' => 'Реализовать систему сбора и валидации входящих данных',
                'Создание дашбордов' => 'Разработать интерактивные дашборды для визуализации',
                'Отчетность' => 'Создать систему генерации отчетов',
                'Интеграция с внешними API' => 'Подключить сторонние сервисы для обогащения данных',
                'Машинное обучение' => 'Внедрить алгоритмы ML для прогнозирования',
            ],
        ];

        $statuses = [TaskStatus::PENDING, TaskStatus::IN_PROGRESS, TaskStatus::COMPLETED];
        $priorities = [TaskPriority::LOW, TaskPriority::MEDIUM, TaskPriority::HIGH];

        foreach ($projects as $project) {
            $projectIndex = $projects->search($project);
            $tasksPerProject = rand(3, 6);

            $templateKey = match ($projectIndex % 3) {
                0 => 'development',
                1 => 'ecommerce',
                2 => 'analytics',
            };

            $templates = $taskTemplates[$templateKey];
            $templateKeys = array_keys($templates);

            for ($i = 0; $i < $tasksPerProject; $i++) {
                $templateIndex = $i % count($templateKeys);
                $taskTitle = $templateKeys[$templateIndex];
                $taskDescription = $templates[$taskTitle];

                $taskStatus = match ($project->status) {
                    ProjectStatus::COMPLETED => TaskStatus::COMPLETED,
                    ProjectStatus::ARCHIVED => TaskStatus::COMPLETED,
                    default => $statuses[array_rand($statuses)]
                };

                $dueDate = match ($taskStatus) {
                    TaskStatus::COMPLETED => $now->copy()->subDays(rand(5, 60)),
                    TaskStatus::IN_PROGRESS => $now->copy()->addDays(rand(1, 14)),
                    TaskStatus::PENDING => $now->copy()->addDays(rand(7, 30)),
                };

                $tasks[] = [
                    'title' => $taskTitle,
                    'description' => $taskDescription . ' для проекта "' . $project->name . '"',
                    'status' => $taskStatus,
                    'priority' => $priorities[array_rand($priorities)],
                    'project_id' => $project->id,
                    'assigned_to' => $regularUsers->random()->id,
                    'created_by' => $managers->isNotEmpty() ? $managers->random()->id : $admin->id,
                    'due_date' => $dueDate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $additionalTasks = [
            ['title' => 'Критическая задача', 'priority' => TaskPriority::HIGH, 'status' => TaskStatus::PENDING, 'days_offset' => 1],
            ['title' => 'Просроченная задача', 'priority' => TaskPriority::MEDIUM, 'status' => TaskStatus::IN_PROGRESS, 'days_offset' => -5],
            ['title' => 'Долгосрочная задача', 'priority' => TaskPriority::LOW, 'status' => TaskStatus::PENDING, 'days_offset' => 45],
        ];

        foreach ($additionalTasks as $task) {
            $tasks[] = [
                'title' => $task['title'],
                'description' => 'Дополнительная задача для тестирования фильтров и сортировок',
                'status' => $task['status'],
                'priority' => $task['priority'],
                'project_id' => $projects->random()->id,
                'assigned_to' => $regularUsers->random()->id,
                'created_by' => $admin->id,
                'due_date' => $now->copy()->addDays($task['days_offset']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Task::insert($tasks);
    }
}
