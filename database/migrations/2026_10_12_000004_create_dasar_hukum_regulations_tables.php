<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dasar_hukum_peraturans', function (Blueprint $table): void {
            $table->id();
            $table->string('module_slug', 100)->index();
            $table->string('nama_peraturan');
            $table->string('document_path');
            $table->string('document_original_name');
            $table->timestamps();
        });

        Schema::create('dasar_hukum_pasals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('peraturan_id')
                ->constrained('dasar_hukum_peraturans')
                ->cascadeOnDelete();
            $table->string('nomor_pasal', 100);
            $table->longText('penjelasan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dasar_hukum_pasals');
        Schema::dropIfExists('dasar_hukum_peraturans');
    }
};
