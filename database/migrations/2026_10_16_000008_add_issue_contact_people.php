<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('isu_strategis', function (Blueprint $table): void {
            $table->string('pic_orang_unit_lembaga_pengusul')->nullable()->after('unit_lembaga_pengusul');
            $table->string('pic_orang_pic')->nullable()->after('pic');
        });
    }

    public function down(): void
    {
        Schema::table('isu_strategis', function (Blueprint $table): void {
            $table->dropColumn(['pic_orang_unit_lembaga_pengusul', 'pic_orang_pic']);
        });
    }
};
