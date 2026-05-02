<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'owner_id', 'vehicle_type_id', 'tonnage', 'volume',
        'tractor_brand', 'tractor_plate', 'tractor_year',
        'trailer_brand', 'trailer_plate', 'trailer_year',
        'temperature_min', 'temperature_max',
        'status',
    ];

    protected $casts = [
        'tonnage' => 'decimal:1',
        'volume' => 'decimal:1',
        'temperature_min' => 'decimal:1',
        'temperature_max' => 'decimal:1',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'vehicle_drivers');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_vehicles');
    }

    public function transportations(): HasMany
    {
        return $this->hasMany(Transportation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeIdle($query)
    {
        return $query->where('status', 'idle');
    }
}
