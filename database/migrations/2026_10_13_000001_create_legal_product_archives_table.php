<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_product_archives', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 10)->index();
            $table->string('nama_dokumen');
            $table->string('document_path');
            $table->string('document_original_name');
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir')->index();
            $table->string('stakeholder');
            $table->string('unit_pelaksana');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_product_archives');
    }
};
