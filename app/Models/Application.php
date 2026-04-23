<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'number', 'author_id', 'client_id', 'contract_id',
        'shipper', 'consignee', 'departure_date', 'arrival_date',
        'departure_city_id', 'destination_city_id',
        'loading_address', 'unloading_address',
        'contact_loading', 'contact_unloading',
        'cargo_name', 'loading_type_id', 'special_conditions',
        'weight', 'volume', 'cargo_cost', 'cargo_currency_id',
        'client_rate', 'client_rate_vat', 'client_rate_currency_id', 'client_rate_exchange',
        'comment', 'status', 'refusal_reason_id', 'refusal_comment',
        'planned_transportations_count',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date' => 'date',
        'weight' => 'decimal:2',
        'volume' => 'decimal:2',
        'cargo_cost' => 'decimal:2',
        'client_rate' => 'decimal:2',
        'client_rate_vat' => 'boolean',
        'client_rate_exchange' => 'decimal:4',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function departureCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'departure_city_id');
    }

    public function destinationCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }

    public function loadingType(): BelongsTo
    {
        return $this->belongsTo(LoadingType::class);
    }

    public function cargoCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'cargo_currency_id');
    }

    public function clientRateCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'client_rate_currency_id');
    }

    public function refusalReason(): BelongsTo
    {
        return $this->belongsTo(RefusalReason::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(ApplicationStop::class)->orderBy('sort_order');
    }

    public function transportations(): HasMany
    {
        return $this->hasMany(Transportation::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'in_transit']);
    }

    public function isRefusal(): bool
    {
        return in_array($this->status, ['client_refusal', 'our_refusal', 'mutual_refusal']);
    }
}
