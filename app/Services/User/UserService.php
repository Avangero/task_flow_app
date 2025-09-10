<?php

namespace App\Services\User;

use App\Exceptions\ApiException;
use App\Models\User as UserModel;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService implements UserServiceInterface
{
    public function __construct(protected readonly UserRepositoryInterface $repository) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * @throws ApiException
     */
    public function show(int $id): UserModel
    {
        $user = $this->repository->findById($id);
        if (! $user) {
            throw new ApiException(__('api.auth.user_not_found'), 404);
        }

        return $user;
    }

    public function update(UserModel $user, array $attributes): UserModel
    {
        return $this->repository->update($user, $attributes);
    }
}
