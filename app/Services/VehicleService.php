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

            $driverId = $data['driver_id'] ?? null;
            unset($data['new_owner'], $data['driver_id']);

            $vehicle = Vehicle::create($data);

            if ($driverId) {
                $vehicle->drivers()->attach($driverId);
            }

            return $vehicle;
        });
    }

    public function search(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return Vehicle::with(['owner', 'vehicleType'])
            ->where(function ($q) use ($query) {
                $q->where('tractor_plate', 'like', "%{$query}%")
                  ->orWhere('tractor_brand', 'like', "%{$query}%")
                  ->orWhere('trailer_plate', 'like', "%{$query}%")
                  ->orWhere('trailer_brand', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();
    }
}

