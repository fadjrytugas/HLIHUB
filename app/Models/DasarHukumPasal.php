<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DasarHukumPasal extends Model
{
    protected $fillable = [
        'peraturan_id',
        'nomor_pasal',
        'penjelasan',
    ];

    public function peraturan(): BelongsTo
    {
        return $this->belongsTo(DasarHukumPeraturan::class, 'peraturan_id');
    }
}
