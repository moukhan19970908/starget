<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Resources\ContractListResource;
use App\Http\Resources\SupplierListResource;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::withCount('transportations')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('bin_iin', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        $total        = Supplier::count();
        $activeTrans  = \App\Models\Transportation::where('status', 'in_progress')->count();
        $legalCount   = Supplier::where('type', 'legal')->count();

        return response()->json([
            'success' => true,
            'data'    => SupplierListResource::collection($suppliers->items()),
            'stats'   => [
                'total'        => $total,
                'active_trans' => $activeTrans,
                'legal'        => $legalCount,
            ],
            'meta'    => [
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
                'per_page'     => $suppliers->perPage(),
                'total'        => $suppliers->total(),
            ],
        ]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $data = array_merge(
            collect($request->safe()->except('documents', 'vehicles'))->toArray(),
            ['status' => 'active']
        );
        $supplier = Supplier::create($data);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('supplier_docs', 'public');
                $supplier->documents()->create([
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        if ($request->filled('vehicles')) {
            foreach ($request->input('vehicles') as $v) {
                $brand = trim($v['tractor_brand'] ?? '');
                $plate = trim($v['tractor_plate'] ?? '');
                if (!$brand && !$plate) continue;
                $vehicle = Vehicle::create([
                    'tractor_brand' => $brand ?: null,
                    'tractor_plate' => $plate ?: null,
                    'status'        => 'idle',
                ]);
                $supplier->vehicles()->attach($vehicle->id);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => new SupplierResource($supplier->load('documents')),
            'message' => 'Поставщик создан. Статус: на проверке.',
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load(['documents', 'vehicles']);

        return response()->json([
            'success' => true,
            'data'    => new SupplierResource($supplier),
        ]);
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->safe()->except('documents'));

        return response()->json([
            'success' => true,
            'data'    => new SupplierResource($supplier->fresh()),
            'message' => 'Поставщик обновлён.',
        ]);
    }

    public function contracts(Supplier $supplier): JsonResponse
    {
        $contracts = $supplier->contracts()->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => ContractListResource::collection($contracts),
        ]);
    }
}
