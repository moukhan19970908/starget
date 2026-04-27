<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Resources\DriverListResource;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use App\Services\DriverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function __construct(private DriverService $service) {}

    public function index(Request $request): JsonResponse
    {
        $drivers = Driver::with(['documents', 'vehicles'])
            ->when($request->search, fn($q) =>
                $q->where('iin', 'like', "%{$request->search}%")
                  ->orWhere('full_name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('full_name')
            ->paginate(20);

        $total      = Driver::count();
        $onTrip     = Driver::where('status', 'on_trip')->count();
        $reserve    = Driver::where('status', 'reserve')->count();
        $expiringDocs = Driver::whereHas('documents', fn($q) =>
            $q->whereNotNull('expires_at')
              ->where('expires_at', '<=', now()->addDays(30))
        )->count();

        $loadPercent = $total > 0 ? round($onTrip / $total * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'data'    => DriverListResource::collection($drivers->items()),
            'meta'    => [
                'current_page' => $drivers->currentPage(),
                'last_page'    => $drivers->lastPage(),
                'per_page'     => $drivers->perPage(),
                'total'        => $drivers->total(),
            ],
            'stats'   => [
                'total'        => $total,
                'on_route'     => $onTrip,
                'load_percent' => $loadPercent,
                'expiring_docs' => $expiringDocs,
                'available'    => $reserve,
            ],
        ]);
    }

    public function store(StoreDriverRequest $request): JsonResponse
    {
        $driver = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new DriverResource($driver->load('documents.documentType')),
            'message' => 'Водитель создан.',
        ], 201);
    }

    public function show(Driver $driver): JsonResponse
    {
        $driver->load(['documents.documentType', 'vehicles']);

        return response()->json([
            'success' => true,
            'data'    => new DriverResource($driver),
        ]);
    }

    public function update(StoreDriverRequest $request, Driver $driver): JsonResponse
    {
        $driver->update($request->safe()->only(['full_name', 'iin', 'type', 'phone', 'license_classes', 'status']));

        return response()->json([
            'success' => true,
            'data'    => new DriverResource($driver->fresh()),
            'message' => 'Водитель обновлён.',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $drivers = $this->service->search($request->iin, $request->phone);

        return response()->json([
            'success' => true,
            'data'    => DriverResource::collection($drivers),
        ]);
    }

    public function addDocument(Request $request, Driver $driver): JsonResponse
    {
        $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'number'           => ['required', 'string'],
            'issued_date'      => ['required', 'date'],
            'issued_by'        => ['required', 'string'],
            'expires_at'       => ['nullable', 'date'],
            'file'             => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $doc = $this->service->addDocument(
            $driver,
            $request->only(['document_type_id', 'number', 'issued_date', 'issued_by', 'expires_at']),
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data'    => $doc,
            'message' => 'Документ добавлен.',
        ], 201);
    }
}
