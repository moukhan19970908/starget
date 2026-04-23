<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Resources\ContractListResource;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contracts = Contract::with(['client', 'supplier'])
            ->when($request->search, fn($q) => $q->where('number', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => ContractListResource::collection($contracts->items()),
            'meta'    => [
                'current_page' => $contracts->currentPage(),
                'last_page'    => $contracts->lastPage(),
                'per_page'     => $contracts->perPage(),
                'total'        => $contracts->total(),
                'total'         => $contracts->total(),
                'active'        => Contract::where('status', 'active')->count(),
                'refused'       => Contract::where('status', 'refused')->count(),
                'completed'     => Contract::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function store(StoreContractRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('contracts', 'public');
        }
        unset($data['file']);

        $contract = Contract::create($data);

        return response()->json([
            'success' => true,
            'data'    => new ContractResource($contract),
            'message' => 'Договор создан.',
        ], 201);
    }

    public function show(Contract $contract): JsonResponse
    {
        $contract->load(['client', 'supplier']);

        return response()->json([
            'success' => true,
            'data'    => new ContractResource($contract),
        ]);
    }

    public function update(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('contracts.edit');

        $data = $request->only([
            'number', 'client_id', 'supplier_id', 'type',
            'signed_date', 'expires_at', 'status',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('contracts', 'public');
        }

        $contract->update($data);

        return response()->json([
            'success' => true,
            'data'    => new ContractResource($contract->fresh()),
            'message' => 'Договор обновлён.',
        ]);
    }

    public function destroy(Contract $contract): JsonResponse
    {
        $this->authorize('contracts.edit');
        $contract->delete();

        return response()->json(['success' => true, 'message' => 'Договор удалён.']);
    }
}
