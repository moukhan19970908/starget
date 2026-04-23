<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransportationRequest;
use App\Http\Resources\TransportationListResource;
use App\Http\Resources\TransportationResource;
use App\Models\Application;
use App\Models\Transportation;
use App\Services\TransportationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransportationController extends Controller
{
    public function __construct(private TransportationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $items = Transportation::with([
            'application.departureCity', 'application.destinationCity',
            'vehicle', 'driver', 'supplier',
        ])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->application_id, fn($q) => $q->where('application_id', $request->application_id))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => TransportationListResource::collection($items->items()),
            'meta'    => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    public function store(StoreTransportationRequest $request, Application $application): JsonResponse
    {
        try {
            $transportation = $this->service->create($application, $request->validated(), $request->user());
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => new TransportationResource($transportation->load(['vehicle', 'driver', 'supplier'])),
            'message' => 'Перевозка создана.',
        ], 201);
    }

    public function show(Transportation $transportation): JsonResponse
    {
        $transportation->load([
            'application.departureCity', 'application.destinationCity',
            'application.client', 'clientManager', 'logisticManager',
            'clientContract', 'vehicle.owner', 'vehicle.vehicleType',
            'driver.documents.documentType', 'supplier', 'supplierContract',
            'documents',
        ]);

        return response()->json([
            'success' => true,
            'data'    => new TransportationResource($transportation),
        ]);
    }

    public function update(StoreTransportationRequest $request, Transportation $transportation): JsonResponse
    {
        $transportation->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new TransportationResource($transportation->fresh()),
            'message' => 'Перевозка обновлена.',
        ]);
    }

    public function uploadCallPhoto(Request $request, Transportation $transportation): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $this->service->uploadCallPhoto($transportation, $request->file('photo'));

        return response()->json([
            'success' => true,
            'message' => 'Фото загружено.',
        ]);
    }

    public function complete(Request $request, Transportation $transportation): JsonResponse
    {
        try {
            $this->service->complete($transportation, $request->user());
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => new TransportationResource($transportation->fresh()),
            'message' => 'Перевозка завершена.',
        ]);
    }

    public function export(Transportation $transportation): BinaryFileResponse
    {
        $path = $this->service->exportToWord($transportation);
        return response()->download($path);
    }

    public function uploadDocument(Request $request, Transportation $transportation): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:25600'],
            'type' => ['required', 'in:driver,accompanying'],
        ]);

        $this->service->uploadDocument($transportation, $request->file('file'), $request->type);

        return response()->json([
            'success' => true,
            'message' => 'Документ загружен.',
        ]);
    }
}
