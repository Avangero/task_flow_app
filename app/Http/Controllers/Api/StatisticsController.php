<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Statistics\IndexStatisticsRequest;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\StatisticsResource;
use App\Models\Project;
use App\Services\Statistics\StatisticsServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected readonly StatisticsServiceInterface $service) {}

    public function index(IndexStatisticsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);
        $summary = $this->service->getSummary();

        return BaseApiResource::success(
            message: __('api.http.success'),
            statusCode: 200,
            data: ['statistics' => new StatisticsResource($summary)]
        );
    }
}
