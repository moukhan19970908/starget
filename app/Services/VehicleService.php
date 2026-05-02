<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverDocument;
use App\Models\Owner;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleService
{
    public function createWithRelations(array $data): Vehicle
    {
        return DB::transaction(function () use ($data) {
            // Create owner if new_owner data provided
            if (!empty($data['new_owner'])) {
                $owner = Owner::create($data['new_owner']);
                $data['owner_id'] = $owner->id;
            }

            $driverId   = $data['driver_id']   ?? null;
            $supplierId = $data['supplier_id']  ?? null;
            unset($data['new_owner'], $data['driver_id'], $data['supplier_id']);

            $vehicle = Vehicle::create($data);

            if ($driverId) {
                $vehicle->drivers()->attach($driverId);
            }
            if ($supplierId) {
                $vehicle->suppliers()->attach($supplierId);
            }

            return $vehicle;
        });
    }

    public function search(string $query, ?int $vehicleTypeId = null, ?int $supplierId = null): \Illuminate\Database\Eloquent\Collection
    {
        return Vehicle::with(['owner', 'vehicleType', 'drivers.documents'])
            ->where(function ($q) use ($query) {
                $q->where('tractor_plate', 'like', "%{$query}%")
                  ->orWhere('tractor_brand', 'like', "%{$query}%")
                  ->orWhere('trailer_plate', 'like', "%{$query}%")
                  ->orWhere('trailer_brand', 'like', "%{$query}%");
            })
            ->when($vehicleTypeId, fn($q) => $q->where('vehicle_type_id', $vehicleTypeId))
            ->when($supplierId, fn($q) => $q->whereHas('suppliers', fn($q2) => $q2->where('suppliers.id', $supplierId)))
            ->limit(20)
            ->get();
    }
}

