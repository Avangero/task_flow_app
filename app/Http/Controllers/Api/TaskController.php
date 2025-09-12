<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\Task\TaskServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected readonly TaskServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $list = $this->service->list(
            filters: $request->only(['status', 'priority', 'project_id', 'assigned_to', 'sort_by', 'sort_direction']),
            perPage: (int) $request->get('per_page', 15)
        );

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: [
                'tasks' => TaskResource::collection($list),
                'meta' => [
                    'current_page' => $list->currentPage(),
                    'last_page' => $list->lastPage(),
                    'per_page' => $list->perPage(),
                    'total' => $list->total(),
                ],
            ]
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $payload = array_merge($request->validated(), [
            'created_by' => $request->user()->id,
        ]);

        $task = $this->service->store($payload);

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 201,
            data: ['task' => new TaskResource($task)]
        );
    }

    public function show(int $id): JsonResponse
    {
        $task = $this->service->show($id);
        $this->authorize('view', $task);

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: ['task' => new TaskResource($task)]
        );
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $task = $this->service->show($id);
        $this->authorize('update', $task);

        $updated = $this->service->update($task, $request->validated());

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: ['task' => new TaskResource($updated)]
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $task = $this->service->show($id);
        $this->authorize('delete', $task);

        $this->service->delete($task);

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200
        );
    }
}
