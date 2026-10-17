<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('isu_strategis', function (Blueprint $table) {
            $table->id();
            $table->string('unit_lembaga_pengusul')->nullable();
            $table->string('stakeholder')->nullable();
            $table->string('klasifikasi_strategis')->nullable();
            $table->text('kegiatan');
            $table->text('deskripsi_kegiatan')->nullable();
            $table->text('rincian_kegiatan')->nullable();
            $table->text('goals')->nullable();
            $table->unsignedTinyInteger('progres')->default(0);
            $table->string('status')->default('PENDING');
            $table->string('pic')->nullable();
            $table->string('sla')->nullable();
            $table->unsignedInteger('sla_hari_kerja')->nullable();
            $table->unsignedInteger('durasi_aktual')->nullable();
            $table->text('hambatan_kendala')->nullable();
            $table->text('dampak')->nullable();
            $table->dateTime('terakhir_di_update')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('unit_lembaga_pengusul');
            $table->index('klasifikasi_strategis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isu_strategis');
    }
};
