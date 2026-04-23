<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStop extends Model
{
    protected $fillable = [
        'application_id', 'stop_type_id', 'address', 'sort_order', 'expected_at',
    ];

    protected $casts = [
        'expected_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function stopType(): BelongsTo
    {
        return $this->belongsTo(StopType::class);
    }
}
