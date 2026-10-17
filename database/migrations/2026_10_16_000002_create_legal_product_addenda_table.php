<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_product_addenda', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('legal_product_archive_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->date('tanggal_adendum');
            $table->text('perubahan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_product_addenda');
    }
};
