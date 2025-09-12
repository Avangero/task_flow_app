<?php

namespace App\Services\Project;

use App\Exceptions\Api\ApiException;
use App\Models\Project as ProjectModel;
use App\Repositories\Project\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class ProjectService implements ProjectServiceInterface
{
    public function __construct(protected ProjectRepositoryInterface $repository) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * @throws ApiException
     */
    public function show(int $id): ProjectModel
    {
        $project = $this->repository->findById($id);
        if (! $project) {
            throw new ApiException(__('api.http.not_found'), 404);
        }

        return $project;
    }

    public function store(array $attributes): ProjectModel
    {
        return $this->repository->create($attributes);
    }

    public function update(ProjectModel $project, array $attributes): ProjectModel
    {
        return $this->repository->update($project, $attributes);
    }

    public function delete(ProjectModel $project): void
    {
        $this->repository->delete($project);
    }
}
