<?php

namespace App\Providers;

use App\Exceptions\Handler as AppExceptionHandler;
use App\Filters\TaskFilter;
use App\Filters\TaskFilterInterface;
use App\Models\Project;
use App\Models\Task;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use App\Repositories\Project\ProjectRepository;
use App\Repositories\Project\ProjectRepositoryInterface;
use App\Repositories\Task\TaskRepository;
use App\Repositories\Task\TaskRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Project\ProjectService;
use App\Services\Project\ProjectServiceInterface;
use App\Services\Statistics\StatisticsService;
use App\Services\Statistics\StatisticsServiceInterface;
use App\Services\Task\TaskService;
use App\Services\Task\TaskServiceInterface;
use App\Services\User\UserService;
use App\Services\User\UserServiceInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ExceptionHandlerContract::class,
            AppExceptionHandler::class
        );

        $this->app->bind(
            AuthServiceInterface::class,
            AuthService::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            UserServiceInterface::class,
            UserService::class
        );

        $this->app->bind(
            ProjectRepositoryInterface::class,
            ProjectRepository::class
        );

        $this->app->bind(
            ProjectServiceInterface::class,
            ProjectService::class
        );

        $this->app->bind(
            TaskFilterInterface::class,
            TaskFilter::class
        );

        $this->app->bind(
            TaskRepositoryInterface::class,
            TaskRepository::class
        );

        $this->app->bind(
            TaskServiceInterface::class,
            TaskService::class
        );

        $this->app->bind(
            StatisticsServiceInterface::class,
            StatisticsService::class
        );
    }

    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by($request->ip() . '|' . strtolower($email)),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->ip()),
            ];
        });
    }
}
