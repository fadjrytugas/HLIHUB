<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stakeholders', function (Blueprint $table): void {
            $table->string('prioritas', 30)->default('2ND PRIORITY');
            $table->text('fungsi_instansi')->nullable();
            $table->string('website_resmi')->nullable();
            $table->text('struktur_organisasi')->nullable();
            $table->text('alamat_kantor')->nullable();
            $table->string('telepon', 100)->nullable();
            $table->string('fax', 100)->nullable();
            $table->string('email_perusahaan')->nullable();
        });

        Schema::create('stakeholder_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stakeholder_id')->constrained()->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->string('jabatan')->nullable();
            $table->string('nama_panggilan')->nullable();
            $table->string('nama_pasangan')->nullable();
            $table->text('nama_anak')->nullable();
            $table->text('nama_keluarga_lainnya')->nullable();
            $table->string('jenis_kelamin', 30)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('media_sosial')->nullable();
            $table->string('telepon_protokol', 100)->nullable();
            $table->string('telepon_sekretaris', 100)->nullable();
            $table->string('telepon_staff', 100)->nullable();
            $table->text('hobi')->nullable();
            $table->text('makanan_favorit')->nullable();
            $table->text('minuman_favorit')->nullable();
            $table->text('olahraga_favorit')->nullable();
            $table->longText('riwayat_pendidikan')->nullable();
            $table->string('foto_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stakeholder_members');

        Schema::table('stakeholders', function (Blueprint $table): void {
            $table->dropColumn([
                'prioritas',
                'fungsi_instansi',
                'website_resmi',
                'struktur_organisasi',
                'alamat_kantor',
                'telepon',
                'fax',
                'email_perusahaan',
            ]);
        });
    }
};
