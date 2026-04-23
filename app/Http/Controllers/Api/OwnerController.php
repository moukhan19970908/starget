<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOwnerRequest;
use App\Http\Resources\OwnerResource;
use App\Models\Owner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $owners = Owner::orderBy('full_name')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => OwnerResource::collection($owners->items()),
            'meta'    => [
                'current_page' => $owners->currentPage(),
                'last_page'    => $owners->lastPage(),
                'per_page'     => $owners->perPage(),
                'total'        => $owners->total(),
            ],
        ]);
    }

    public function store(StoreOwnerRequest $request): JsonResponse
    {
        $owner = Owner::create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new OwnerResource($owner),
            'message' => 'Владелец создан.',
        ], 201);
    }

    public function show(Owner $owner): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new OwnerResource($owner),
        ]);
    }

    public function update(StoreOwnerRequest $request, Owner $owner): JsonResponse
    {
        $owner->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new OwnerResource($owner->fresh()),
            'message' => 'Владелец обновлён.',
        ]);
    }
}
