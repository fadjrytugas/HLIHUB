<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalProductAddendum extends Model
{
    protected $table = 'legal_product_addenda';

    protected $fillable = [
        'tanggal_adendum',
        'perubahan',
    ];

    protected $casts = [
        'tanggal_adendum' => 'date',
    ];

    public function legalProductArchive(): BelongsTo
    {
        return $this->belongsTo(LegalProductArchive::class);
    }
}
