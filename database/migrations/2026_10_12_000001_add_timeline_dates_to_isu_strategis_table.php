<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('isu_strategis', function (Blueprint $table): void {
            $table->date('tanggal_masuk')->nullable()->after('dampak');
            $table->date('deadline')->nullable()->after('tanggal_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('isu_strategis', function (Blueprint $table): void {
            $table->dropColumn(['tanggal_masuk', 'deadline']);
        });
    }
};
