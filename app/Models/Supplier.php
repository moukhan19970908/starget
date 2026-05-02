<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'bin_iin', 'type', 'specialization',
        'contact_name', 'phone', 'email',
        'comment', 'status',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(SupplierDocument::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'supplier_vehicles');
    }

    public function transportations(): HasMany
    {
        return $this->hasMany(Transportation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
