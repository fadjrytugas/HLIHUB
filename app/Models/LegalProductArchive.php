<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalProductArchive extends Model
{
    protected $fillable = [
        'type',
        'nama_dokumen',
        'document_path',
        'document_original_name',
        'tanggal_mulai',
        'tanggal_berakhir',
        'jangka_waktu',
        'stakeholder',
        'unit_pelaksana',
        'ruang_lingkup',
        'penandatangan_pihak_pertama',
        'penandatangan_pihak_kedua',
        'korespondensi_pihak_pertama',
        'korespondensi_pihak_kedua',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    public function getJangkaWaktuAttribute(?string $value): ?string
    {
        if ($value !== null && trim($value) !== '') {
            return $value;
        }

        if ($this->tanggal_mulai === null || $this->tanggal_berakhir === null) {
            return null;
        }

        $duration = $this->tanggal_mulai->diff($this->tanggal_berakhir);
        $parts = [];

        foreach ([
            'y' => 'tahun',
            'm' => 'bulan',
            'd' => 'hari',
        ] as $unit => $label) {
            if ($duration->{$unit} > 0) {
                $parts[] = $duration->{$unit}.' '.$label;
            }
        }

        return $parts === [] ? 'Kurang dari 1 hari' : implode(' ', $parts);
    }

    public function isExpired(?Carbon $today = null): bool
    {
        if ($this->tanggal_berakhir === null) {
            return false;
        }

        $today = ($today ?? Carbon::today())->copy()->startOfDay();

        return $this->tanggal_berakhir->copy()->startOfDay()->lessThan($today);
    }

    public function isExpiringWithinThreeMonths(?Carbon $today = null): bool
    {
        if ($this->tanggal_berakhir === null || $this->isExpired($today)) {
            return false;
        }

        $today = ($today ?? Carbon::today())->copy()->startOfDay();

        return $this->tanggal_berakhir->copy()->startOfDay()
            ->lessThanOrEqualTo($today->addMonthsNoOverflow(3));
    }

    public function addenda(): HasMany
    {
        return $this->hasMany(LegalProductAddendum::class)->orderBy('tanggal_adendum')->orderBy('id');
    }
}
