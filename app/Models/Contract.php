<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'number', 'client_id', 'supplier_id', 'type',
        'signed_date', 'expires_at', 'file_path', 'status',
    ];

    protected $casts = [
        'signed_date' => 'date',
        'expires_at' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query)
    {
        return $query->where('status', 'expiring');
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at && $this->expires_at->lte(now()->addDays(30));
    }
}
