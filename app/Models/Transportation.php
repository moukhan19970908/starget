<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transportation extends Model
{
    protected $fillable = [
        'number', 'application_id', 'organization',
        'client_manager_id', 'logistic_manager_id', 'client_contract_id',
        'vat_kz', 'vehicle_id', 'driver_id',
        'supplier_id', 'supplier_contract_id',
        'supplier_delay_days', 'supplier_rate', 'supplier_rate_currency',
        'call_photo_path', 'status',
    ];

    protected $casts = [
        'vat_kz' => 'boolean',
        'supplier_rate' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function clientManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_manager_id');
    }

    public function logisticManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logistic_manager_id');
    }

    public function clientContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'client_contract_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplierContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'supplier_contract_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TransportationDocument::class);
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
