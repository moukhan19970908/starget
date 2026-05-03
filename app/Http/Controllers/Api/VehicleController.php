<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Resources\VehicleListResource;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(private VehicleService $service) {}

    public function index(Request $request): JsonResponse
    {
        $vehicles = Vehicle::with(['owner', 'vehicleType'])
            ->when($request->search, fn($q) =>
                $q->where('tractor_plate', 'like', "%{$request->search}%")
                  ->orWhere('tractor_brand', 'like', "%{$request->search}%")
                  ->orWhereHas('owner', fn($q2) => $q2->where('full_name', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->whereHas('vehicleType', fn($q2) => $q2->where('name', $request->type)))
            ->orderByDesc('created_at')
            ->paginate(20);

        $total    = Vehicle::count();
        $inTransit = Vehicle::where('status', 'in_transit')->count();
        $idle     = Vehicle::where('status', 'idle')->count();
        $repair   = Vehicle::where('status', 'repair')->count();

        return response()->json([
            'success' => true,
            'data'    => VehicleListResource::collection($vehicles->items()),
            'meta'    => [
                'current_page' => $vehicles->currentPage(),
                'last_page'    => $vehicles->lastPage(),
                'per_page'     => $vehicles->perPage(),
                'total'        => $vehicles->total(),
                'total_count'  => $total,
                'in_transit'   => $inTransit,
                'idle'         => $idle,
                'repair'       => $repair,
            ],
        ]);
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->service->createWithRelations($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new VehicleResource($vehicle->load(['owner', 'vehicleType', 'drivers', 'documents'])),
            'message' => 'Транспорт добавлен.',
        ], 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        $vehicle->load(['owner.documents.documentType', 'vehicleType', 'drivers', 'suppliers', 'documents']);

        return response()->json([
            'success' => true,
            'data'    => new VehicleResource($vehicle),
        ]);
    }

    public function update(StoreVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $vehicle->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new VehicleResource($vehicle->fresh()),
            'message' => 'Транспорт обновлён.',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $vehicles = $this->service->search(
            (string) $request->get('q', ''),
            $request->integer('vehicle_type_id') ?: null,
            $request->integer('supplier_id') ?: null,
        );

        return response()->json([
            'success' => true,
            'data'    => VehicleListResource::collection($vehicles),
        ]);
    }
}
