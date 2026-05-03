<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'type', 'full_name', 'phone', 'iin', 'license_classes', 'status', 'is_owner',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(DriverDocument::class);
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_drivers');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnTrip($query)
    {
        return $query->where('status', 'on_trip');
    }

    public function hasExpiringDocuments(): bool
    {
        return $this->documents()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(30))
            ->exists();
    }
}
