<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Health\HealthCheckService;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function __invoke(HealthCheckService $health): JsonResponse {
        $result = $health->check();
        $statusCode = $result['status'] === 'ok' ? 200 : 503;
        return response()->json($result, $statusCode);
    }
}
