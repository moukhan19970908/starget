<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name', 'bin_iin', 'type',
        'contact_name', 'phone', 'email',
        'legal_address', 'actual_address', 'status',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function activeContractsCount(): int
    {
        return $this->contracts()->where('status', 'active')->count();
    }
}
