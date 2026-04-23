<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportationDocument extends Model
{
    protected $fillable = ['transportation_id', 'type', 'file_path', 'original_name'];

    public function transportation(): BelongsTo
    {
        return $this->belongsTo(Transportation::class);
    }
}
