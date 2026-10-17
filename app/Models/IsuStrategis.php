<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsuStrategis extends Model
{
    protected $table = 'isu_strategis';

    protected $primaryKey = 'no';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'unit_lembaga_pengusul',
        'pic_orang_unit_lembaga_pengusul',
        'stakeholder',
        'klasifikasi_strategis',
        'kegiatan',
        'deskripsi_kegiatan',
        'rincian_kegiatan',
        'goals',
        'progres',
        'status',
        'pic',
        'pic_orang_pic',
        'sla',
        'sla_hari_kerja',
        'durasi_aktual',
        'hambatan_kendala',
        'dampak',
        'tanggal_masuk',
        'deadline',
        'terakhir_di_update',
        'keterangan_update',
    ];

    protected $casts = [
        'progres' => 'integer',
        'sla_hari_kerja' => 'integer',
        'durasi_aktual' => 'integer',
        'terakhir_di_update' => 'datetime',
        'tanggal_masuk' => 'date',
        'deadline' => 'date',
    ];

    public function tracking()
    {
        return $this->hasMany(IsuStrategisTracking::class, 'isu_strategis_no', 'no')->orderBy('tahap_ke');
    }

    public function isSlaUnassigned(): bool
    {
        return $this->no >= 1
            && $this->no <= 50
            && $this->getAttribute('sla') === null
            && $this->getAttribute('sla_hari_kerja') === null;
    }

    public function getDurasiAktualAttribute($value): ?int
    {
        if ($this->isSlaUnassigned()) {
            return null;
        }

        if (!$this->relationLoaded('tracking')) {
            return $value === null ? null : (int) $value;
        }

        $tracking = $this->tracking->where('status', '!=', 'REMOVED');
        $active = $tracking->firstWhere('status', 'ACTIVE');
        if ($active) {
            return IsuStrategisTracking::businessDaysElapsed($active->mulai_pada);
        }

        $completed = $tracking
            ->where('status', 'COMPLETED')
            ->sortByDesc('selesai_pada')
            ->first();
        if (!$completed) {
            return $value === null ? null : (int) $value;
        }

        return $value !== null
            ? (int) $value
            : IsuStrategisTracking::businessDaysElapsed($completed->mulai_pada, $completed->selesai_pada);
    }
}
