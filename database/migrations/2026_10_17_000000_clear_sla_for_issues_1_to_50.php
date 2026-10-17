<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('isu_strategis')
            ->whereBetween('no', [1, 50])
            ->update([
                'sla' => null,
                'sla_hari_kerja' => null,
                'durasi_aktual' => null,
            ]);
    }

    public function down(): void
    {
    }
};
