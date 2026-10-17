<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('isu_strategis', function (Blueprint $table): void {
            $table->text('keterangan_update')->nullable()->after('terakhir_di_update');
        });
    }

    public function down(): void
    {
        Schema::table('isu_strategis', function (Blueprint $table): void {
            $table->dropColumn('keterangan_update');
        });
    }
};
