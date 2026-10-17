<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE isu_strategis MODIFY kegiatan TEXT NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE isu_strategis ALTER COLUMN kegiatan TYPE TEXT');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE isu_strategis MODIFY kegiatan VARCHAR(255) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE isu_strategis ALTER COLUMN kegiatan TYPE VARCHAR(255)');
        }
    }
};
