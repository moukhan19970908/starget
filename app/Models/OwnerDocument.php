<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerDocument extends Model
{
    protected $fillable = [
        'owner_id', 'document_type_id', 'number',
        'issued_date', 'issued_by', 'iin', 'file_path',
    ];

    protected $casts = [
        'issued_date' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }
}
