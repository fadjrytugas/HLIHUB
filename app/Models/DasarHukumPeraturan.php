<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DasarHukumPeraturan extends Model
{
    protected $fillable = [
        'module_slug',
        'nama_peraturan',
        'document_path',
        'document_original_name',
    ];

    public function pasal(): HasMany
    {
        return $this->hasMany(DasarHukumPasal::class, 'peraturan_id')->orderBy('id');
    }
}
