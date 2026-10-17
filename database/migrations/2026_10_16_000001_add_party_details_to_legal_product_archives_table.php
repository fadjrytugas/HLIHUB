<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_product_archives', function (Blueprint $table): void {
            $table->text('ruang_lingkup')->nullable();
            $table->string('penandatangan_pihak_pertama')->nullable();
            $table->string('penandatangan_pihak_kedua')->nullable();
            $table->text('korespondensi_pihak_pertama')->nullable();
            $table->text('korespondensi_pihak_kedua')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('legal_product_archives', function (Blueprint $table): void {
            $table->dropColumn([
                'ruang_lingkup',
                'penandatangan_pihak_pertama',
                'penandatangan_pihak_kedua',
                'korespondensi_pihak_pertama',
                'korespondensi_pihak_kedua',
            ]);
        });
    }
};
