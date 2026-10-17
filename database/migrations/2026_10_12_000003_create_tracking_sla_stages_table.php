<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_sla_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('tahap_ke')->unique();
            $table->string('nama_tahap');
            $table->unsignedInteger('sla_hari_kerja')->default(0);
            $table->timestamps();
        });

        $stages = [
            'Usulan Masuk',
            'Mapping Issue',
            'Mapping Stakeholder',
            'Analisa Isu Strategis (Paparan, RDS)',
            'Reviu & Harmonisasi Nota Kesepahaman (MoU)',
            'Reviu & Harmonisasi Perjanjian Kerja Sama (PKS)',
            'Penyusunan dan Pengidentifikasian Legal Basis dan Legal Issue (Legal Analysis)',
            'Resume Project Monitoring Progress',
        ];

        foreach ($stages as $index => $name) {
            \Illuminate\Support\Facades\DB::table('tracking_sla_stages')->insert([
                'tahap_ke' => $index + 1,
                'nama_tahap' => $name,
                'sla_hari_kerja' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_sla_stages');
    }
};
