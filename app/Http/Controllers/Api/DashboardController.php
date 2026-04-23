<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(Request $request): JsonResponse
    {
        $period = $request->get('period', 'this_month');

        return response()->json([
            'success' => true,
            'data'    => [
                'financial'       => $this->dashboardService->getKpi($period),
                'operational'     => $this->dashboardService->getOperational(),
                'chart'           => $this->dashboardService->getChart($period),
                'recent_activity' => $this->dashboardService->getRecentActivity(5),
            ],
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        // Placeholder for PDF/Excel export
        return response()->json([
            'success' => false,
            'message' => 'Экспорт пока не реализован.',
        ], 501);
    }
}
