<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\Project\ProjectServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected readonly ProjectServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $list = $this->service->list(
            filters: $request->only(['status']),
            perPage: (int) $request->get('per_page', 15)
        );

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: [
                'projects' => ProjectResource::collection($list),
                'meta' => [
                    'current_page' => $list->currentPage(),
                    'last_page' => $list->lastPage(),
                    'per_page' => $list->perPage(),
                    'total' => $list->total(),
                ],
            ]
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $payload = array_merge($request->validated(), [
            'created_by' => $request->user()->id,
        ]);

        $project = $this->service->store($payload);

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 201,
            data: ['project' => new ProjectResource($project)]
        );
    }

    public function show(int $id): JsonResponse
    {
        $project = $this->service->show($id);
        $this->authorize('view', $project);

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: ['project' => new ProjectResource($project)]
        );
    }

    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        $project = $this->service->show($id);
        $this->authorize('update', $project);

        $updated = $this->service->update($project, $request->validated());

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: ['project' => new ProjectResource($updated)]
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $project = $this->service->show($id);
        $this->authorize('delete', $project);

        $this->service->delete($project);

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200
        );
    }
}
