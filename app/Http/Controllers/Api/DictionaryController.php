<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Currency;
use App\Models\DocumentType;
use App\Models\LoadingType;
use App\Models\RefusalReason;
use App\Models\StopType;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;

class DictionaryController extends Controller
{
    public function cities(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => City::orderBy('name')->get(['id', 'name', 'country'])]);
    }

    public function currencies(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Currency::all(['id', 'code', 'name'])]);
    }

    public function loadingTypes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => LoadingType::all(['id', 'name'])]);
    }

    public function vehicleTypes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => VehicleType::all(['id', 'name', 'is_ref'])]);
    }

    public function documentTypes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => DocumentType::all(['id', 'name'])]);
    }

    public function stopTypes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => StopType::all(['id', 'name'])]);
    }

    public function refusalReasons(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => RefusalReason::all(['id', 'name', 'type'])]);
    }
}
