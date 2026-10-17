<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class IsuStrategisTracking extends Model
{
    protected $table = 'isu_strategis_trackings';

    protected $fillable = [
        'isu_strategis_no',
        'tahap_ke',
        'nama_tahap',
        'sla_hari_kerja',
        'status',
        'mulai_pada',
        'selesai_pada',
    ];

    protected $casts = [
        'mulai_pada' => 'datetime',
        'selesai_pada' => 'datetime',
        'sla_hari_kerja' => 'integer',
    ];

    public function isuStrategis()
    {
        return $this->belongsTo(IsuStrategis::class, 'isu_strategis_no', 'no');
    }

    public function getDeadlinePadaAttribute(): ?Carbon
    {
        if (!$this->mulai_pada) {
            return null;
        }

        $date = $this->mulai_pada->copy();
        $remaining = (int) $this->sla_hari_kerja;
        while ($remaining > 0) {
            $date->addDay();
            if ($date->isWeekday()) {
                $remaining--;
            }
        }

        return $date;
    }

    public static function businessDaysElapsed(?Carbon $startedAt, ?Carbon $endedAt = null): int
    {
        if (!$startedAt) {
            return 0;
        }

        $cursor = $startedAt->copy()->startOfDay()->addDay();
        $end = ($endedAt ?? now())->copy()->startOfDay();
        $days = 0;

        while ($cursor->lte($end)) {
            if ($cursor->isWeekday()) {
                $days++;
            }
            $cursor->addDay();
        }

        return $days;
    }
}
