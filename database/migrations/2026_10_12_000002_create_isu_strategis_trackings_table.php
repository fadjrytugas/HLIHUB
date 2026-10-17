<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('isu_strategis_trackings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('isu_strategis_no');
            $table->unsignedTinyInteger('tahap_ke');
            $table->string('nama_tahap');
            $table->unsignedInteger('sla_hari_kerja')->default(0);
            $table->string('status')->default('PENDING');
            $table->dateTime('mulai_pada')->nullable();
            $table->dateTime('selesai_pada')->nullable();
            $table->timestamps();
            $table->unique(['isu_strategis_no', 'tahap_ke']);
            $table->index(['isu_strategis_no', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isu_strategis_trackings');
    }
};
