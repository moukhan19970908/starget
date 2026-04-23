<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Http\Requests\ChangeApplicationStatusRequest;
use App\Http\Requests\StoreApplicationStopRequest;
use App\Http\Resources\ApplicationListResource;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\ApplicationStopResource;
use App\Models\Application;
use App\Models\ApplicationStop;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $apps = Application::with(['client', 'departureCity', 'destinationCity', 'author', 'clientRateCurrency'])
            ->when($request->search, fn($q) => $q->where('number', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->author_id, fn($q) => $q->where('author_id', $request->author_id))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate(20);

        // Mini-KPI
        $refusalCount = Application::whereIn('status', ['client_refusal', 'our_refusal', 'mutual_refusal'])->count();
        $totalCount   = Application::count();
        $refusalRate  = $totalCount > 0 ? round($refusalCount / $totalCount * 100, 1) : 0;
        $expectedPayments = Application::where('status', 'open')->sum('client_rate');

        return response()->json([
            'success' => true,
            'data'    => ApplicationListResource::collection($apps->items()),
            'meta'    => [
                'current_page'      => $apps->currentPage(),
                'last_page'         => $apps->lastPage(),
                'per_page'          => $apps->perPage(),
                'total'             => $apps->total(),
                'refusal_rate_percent' => $refusalRate,
                'expected_payments'    => $expectedPayments,
            ],
        ]);
    }

    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $isDraft = $request->boolean('draft');
        $app = $isDraft
            ? $this->service->saveDraft($request->validated(), $request->user())
            : $this->service->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data'    => new ApplicationResource($app->load(['client', 'departureCity', 'destinationCity', 'stops.stopType', 'author'])),
            'message' => $isDraft ? 'Черновик сохранён.' : 'Заявка создана.',
        ], 201);
    }

    public function show(Application $application): JsonResponse
    {
        $application->load([
            'client', 'contract', 'departureCity', 'destinationCity',
            'loadingType', 'cargoCurrency', 'clientRateCurrency',
            'refusalReason', 'author', 'stops.stopType',
            'transportations.vehicle', 'transportations.driver',
        ]);

        return response()->json([
            'success' => true,
            'data'    => new ApplicationResource($application),
        ]);
    }

    public function update(UpdateApplicationRequest $request, Application $application): JsonResponse
    {
        $app = $this->service->update($application, $request->validated());

        return response()->json([
            'success' => true,
            'data'    => new ApplicationResource($app),
            'message' => 'Заявка обновлена.',
        ]);
    }

    public function changeStatus(ChangeApplicationStatusRequest $request, Application $application): JsonResponse
    {
        try {
            $this->service->changeStatus($application, $request->status, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => new ApplicationResource($application->fresh()),
            'message' => 'Статус заявки изменён.',
        ]);
    }

    public function storeStop(StoreApplicationStopRequest $request, Application $application): JsonResponse
    {
        $maxOrder = $application->stops()->max('sort_order') ?? 0;
        $stop = $application->stops()->create(array_merge($request->validated(), [
            'sort_order' => $request->sort_order ?? $maxOrder + 1,
        ]));

        return response()->json([
            'success' => true,
            'data'    => new ApplicationStopResource($stop->load('stopType')),
            'message' => 'Точка добавлена.',
        ], 201);
    }

    public function updateStop(StoreApplicationStopRequest $request, Application $application, ApplicationStop $stop): JsonResponse
    {
        $stop->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new ApplicationStopResource($stop->load('stopType')),
            'message' => 'Точка обновлена.',
        ]);
    }

    public function destroyStop(Application $application, ApplicationStop $stop): JsonResponse
    {
        $stop->delete();

        return response()->json(['success' => true, 'message' => 'Точка удалена.']);
    }
}
