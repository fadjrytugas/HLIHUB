<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stakeholder extends Model
{
    protected $fillable = [
        'nama',
        'prioritas',
        'fungsi_instansi',
        'website_resmi',
        'struktur_organisasi',
        'alamat_kantor',
        'telepon',
        'fax',
        'email_perusahaan',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(StakeholderMember::class);
    }
}
