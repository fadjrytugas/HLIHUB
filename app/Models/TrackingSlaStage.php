<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingSlaStage extends Model
{
    protected $table = 'tracking_sla_stages';

    protected $fillable = [
        'tahap_ke',
        'nama_tahap',
        'sla_hari_kerja',
    ];

    protected $casts = [
        'tahap_ke' => 'integer',
        'sla_hari_kerja' => 'integer',
    ];
}
