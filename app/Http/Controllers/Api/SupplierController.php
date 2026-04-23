<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Resources\ContractListResource;
use App\Http\Resources\SupplierListResource;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
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
            ->orderBy('name')
            ->paginate(20);

        $total         = Supplier::count();
        $activeCount   = Supplier::where('status', 'active')->count();
        $legalPercent  = $total > 0 ? round(Supplier::where('type', 'legal')->count() / $total * 100) : 0;
        $pendingCount  = Supplier::where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'data'    => SupplierListResource::collection($suppliers->items()),
            'meta'    => [
                'current_page'   => $suppliers->currentPage(),
                'last_page'      => $suppliers->lastPage(),
                'per_page'       => $suppliers->perPage(),
                'total'          => $suppliers->total(),
                'total_count'    => $total,
                'active_count'   => $activeCount,
                'legal_percent'  => $legalPercent,
                'pending_count'  => $pendingCount,
            ],
        ]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->safe()->except('documents'));

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('supplier_docs', 'public');
                $supplier->documents()->create([
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
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
        $supplier->load(['documents']);

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
