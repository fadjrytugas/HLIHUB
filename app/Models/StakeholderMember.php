<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StakeholderMember extends Model
{
    protected $fillable = [
        'stakeholder_id',
        'nama_lengkap',
        'jabatan',
        'nama_panggilan',
        'nama_pasangan',
        'nama_anak',
        'nama_keluarga_lainnya',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'media_sosial',
        'telepon_protokol',
        'telepon_sekretaris',
        'telepon_staff',
        'hobi',
        'makanan_favorit',
        'minuman_favorit',
        'olahraga_favorit',
        'riwayat_pendidikan',
        'sumber_data_url',
        'foto_path',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function stakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class);
    }
}
