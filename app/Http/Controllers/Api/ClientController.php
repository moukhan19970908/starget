<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientListResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ContractListResource;
use App\Http\Resources\TaskResource;
use App\Models\Client;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clients = Client::withCount(['contracts as active_contracts_count' => fn($q) => $q->where('status', 'active')])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('bin_iin', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(20);

        $total    = Client::count();
        $inactive = Client::where('status', 'inactive')->count();

        $pendingTasks = Task::with(['creator', 'application'])
            ->where('status', 'open')
            ->whereIn('type', ['create_client'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ClientListResource::collection($clients->items()),
            'meta'    => [
                'current_page'   => $clients->currentPage(),
                'last_page'      => $clients->lastPage(),
                'per_page'       => $clients->perPage(),
                'total'          => $clients->total(),
                'total_clients'  => $total,
                'inactive_count' => $inactive,
                'pending_tasks'  => TaskResource::collection($pendingTasks),
            ],
        ]);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new ClientResource($client),
            'message' => 'Клиент создан.',
        ], 201);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new ClientResource($client),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new ClientResource($client->fresh()),
            'message' => 'Клиент обновлён.',
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('clients.delete');
        $client->delete();

        return response()->json(['success' => true, 'message' => 'Клиент удалён.']);
    }

    public function contracts(Client $client): JsonResponse
    {
        $contracts = $client->contracts()->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => ContractListResource::collection($contracts),
        ]);
    }
}
