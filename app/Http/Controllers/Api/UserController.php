<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\User\UserServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly UserServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $list = $this->service->list(
            filters: $request->only(['status', 'role_id']),
            perPage: (int) $request->get('per_page', 15)
        );

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: [
                'users' => UserResource::collection($list),
                'meta' => [
                    'current_page' => $list->currentPage(),
                    'last_page' => $list->lastPage(),
                    'per_page' => $list->perPage(),
                    'total' => $list->total(),
                ],
            ]
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->service->show($id);
        $this->authorize('view', $user);

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: ['user' => new UserResource($user)]
        );
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->service->show($id);
        $this->authorize('update', $user);

        $updated = $this->service->update($user, $request->validated());

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: ['user' => new UserResource($updated)]
        );
    }
}
