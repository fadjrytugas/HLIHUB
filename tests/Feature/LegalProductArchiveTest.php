<?php

namespace Tests\Feature;

use App\Models\LegalProductArchive;
use App\Models\IsuStrategis;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegalProductArchiveTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_a_legal_product_archive_can_be_uploaded_and_downloaded(): void
    {
        Storage::fake('local');

        $this->post(route('legal-products.store', 'pks'), [
            'nama_dokumen' => 'PKS Kerja Sama Pengujian',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2027-01-01',
            'jangka_waktu' => '1 tahun',
            'stakeholder' => 'Kementerian Pengujian',
            'unit_pelaksana' => 'Direktorat Kerja Sama',
            'ruang_lingkup' => 'Pengembangan sistem dan pertukaran data.',
            'penandatangan_pihak_pertama' => 'Direktur HLI',
            'penandatangan_pihak_kedua' => 'Kepala Kementerian',
            'korespondensi_pihak_pertama' => "Sekretariat HLI\nJalan Merdeka\nTelepon: 021-123\nSurel: hli@example.test",
            'korespondensi_pihak_kedua' => "Bagian Kerja Sama\nJalan Pengujian\nTelepon: 021-456\nSurel: mitra@example.test",
            'document' => UploadedFile::fake()->createWithContent('pks-pengujian.pdf', "%PDF-1.4\nSample PDF\n%%EOF"),
        ])->assertRedirect(route('legal-products.index', 'pks'))
            ->assertSessionHasNoErrors();

        $archive = LegalProductArchive::query()
            ->where('nama_dokumen', 'PKS Kerja Sama Pengujian')
            ->firstOrFail();

        $this->assertSame('pks', $archive->type);
        $this->assertSame('pks-pengujian.pdf', $archive->document_original_name);
        $this->assertSame('1 tahun', $archive->jangka_waktu);
        Storage::disk('local')->assertExists($archive->document_path);

        $this->get(route('legal-products.download', ['pks', $archive]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=pks-pengujian.pdf');
    }

    public function test_expiry_colors_and_dashboard_notifications_cover_upcoming_and_expired_archives(): void
    {
        Carbon::setTestNow('2026-10-13 12:00:00');
        $expiringSoon = $this->createArchive('PKS Segera Berakhir', '2026-11-13');
        $expiringInThreeMonths = $this->createArchive('MoU Berakhir Tiga Bulan Lagi', '2027-01-13', 'mou');
        $expiredRecently = $this->createArchive('MoU Baru Berakhir', '2026-10-06', 'mou');
        $expiredLongAgo = $this->createArchive('PKS Sudah Lama Berakhir', '2020-10-05');
        $expiresOutsideThreeMonths = $this->createArchive('MoU Belum Dekat Masa Akhir', '2027-01-14', 'mou');

        $this->get('/')
            ->assertOk()
            ->assertSee('PKS akan berakhir')
            ->assertSee('PKS Segera Berakhir')
            ->assertSee('PKS akan berakhir (H-3 bulan)')
            ->assertSee('MoU Berakhir Tiga Bulan Lagi')
            ->assertSee('MoU sudah berakhir')
            ->assertSee('MoU Baru Berakhir')
            ->assertSee('PKS Sudah Lama Berakhir')
            ->assertDontSee('MoU Belum Dekat Masa Akhir');

        $this->get(route('legal-products.index', 'pks'))
            ->assertOk()
            ->assertSee('legal-product-card--expiring', false)
            ->assertSee('legal-product-card--expired', false)
            ->assertSee('PKS Segera Berakhir')
            ->assertSee('PKS Sudah Lama Berakhir');

        $this->assertTrue($expiringSoon->isExpiringWithinThreeMonths(Carbon::today()));
        $this->assertTrue($expiringInThreeMonths->isExpiringWithinThreeMonths(Carbon::today()));
        $this->assertTrue($expiredRecently->isExpired(Carbon::today()));
        $this->assertTrue($expiredLongAgo->isExpired(Carbon::today()));
        $this->assertFalse($expiresOutsideThreeMonths->isExpiringWithinThreeMonths(Carbon::today()));
    }

    public function test_an_archive_can_be_updated_and_deleted_with_its_document(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('produk-hukum/mou/old.pdf', 'old document');
        $archive = LegalProductArchive::query()->create([
            'type' => 'mou',
            'nama_dokumen' => 'MoU Lama',
            'document_path' => 'produk-hukum/mou/old.pdf',
            'document_original_name' => 'old.pdf',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2026-12-31',
            'stakeholder' => 'Stakeholder Lama',
            'unit_pelaksana' => 'Unit Lama',
        ]);

        $this->put(route('legal-products.update', ['mou', $archive]), [
            'nama_dokumen' => 'MoU Diperbarui',
            'tanggal_mulai' => '2026-02-01',
            'tanggal_berakhir' => '2027-02-01',
            'jangka_waktu' => '1 tahun',
            'stakeholder' => 'Stakeholder Baru',
            'unit_pelaksana' => 'Unit Baru',
            'ruang_lingkup' => 'Ruang lingkup yang diperbarui.',
            'penandatangan_pihak_pertama' => 'Direktur Baru',
            'penandatangan_pihak_kedua' => 'Mitra Baru',
            'korespondensi_pihak_pertama' => 'Alamat pihak pertama yang baru.',
            'korespondensi_pihak_kedua' => 'Alamat pihak kedua yang baru.',
            'document' => UploadedFile::fake()->create('mou-baru.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('legal-products.index', 'mou'))
            ->assertSessionHasNoErrors();

        $archive->refresh();
        $this->assertSame('MoU Diperbarui', $archive->nama_dokumen);
        $this->assertSame('mou-baru.pdf', $archive->document_original_name);
        Storage::disk('local')->assertMissing('produk-hukum/mou/old.pdf');
        Storage::disk('local')->assertExists($archive->document_path);

        $newDocumentPath = $archive->document_path;
        $this->delete(route('legal-products.destroy', ['mou', $archive]))
            ->assertRedirect(route('legal-products.index', 'mou'));

        $this->assertDatabaseMissing('legal_product_archives', ['id' => $archive->id]);
        Storage::disk('local')->assertMissing($newDocumentPath);
    }

    public function test_archive_dates_must_be_valid_and_records_cannot_be_accessed_through_another_type(): void
    {
        Storage::fake('local');
        $this->from(route('legal-products.index', 'pks'))
            ->post(route('legal-products.store', 'pks'), [
                'nama_dokumen' => 'Tanggal Tidak Valid',
                'tanggal_mulai' => '2026-12-01',
                'tanggal_berakhir' => '2026-11-01',
                'jangka_waktu' => '2 tahun',
                'stakeholder' => 'Stakeholder',
                'unit_pelaksana' => 'Unit',
                'ruang_lingkup' => 'Ruang lingkup',
                'penandatangan_pihak_pertama' => 'Penandatangan pertama',
                'penandatangan_pihak_kedua' => 'Penandatangan kedua',
                'korespondensi_pihak_pertama' => 'Korespondensi pertama',
                'korespondensi_pihak_kedua' => 'Korespondensi kedua',
                'document' => UploadedFile::fake()->create('invalid.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('tanggal_berakhir');

        $archive = $this->createArchive('Arsip PKS', '2027-01-01');
        $this->from(route('legal-products.index', 'pks'))
            ->put(route('legal-products.update', ['pks', $archive]), [
                'archive_id' => $archive->id,
                'nama_dokumen' => 'Nama dari formulir edit',
                'tanggal_mulai' => '2026-12-01',
                'tanggal_berakhir' => '2026-11-01',
                'jangka_waktu' => '2 tahun',
                'stakeholder' => 'Stakeholder',
                'unit_pelaksana' => 'Unit',
                'ruang_lingkup' => 'Ruang lingkup',
                'penandatangan_pihak_pertama' => 'Penandatangan pertama',
                'penandatangan_pihak_kedua' => 'Penandatangan kedua',
                'korespondensi_pihak_pertama' => 'Korespondensi pertama',
                'korespondensi_pihak_kedua' => 'Korespondensi kedua',
            ])
            ->assertSessionHasErrors('tanggal_berakhir');

        $this->get(route('legal-products.index', 'pks'))
            ->assertOk()
            ->assertSee('id="archive-edit-'.$archive->id.'"', false)
            ->assertSee('data-reopen', false)
            ->assertSee('value="Nama dari formulir edit"', false);

        $this->get(route('legal-products.download', ['mou', $archive]))
            ->assertNotFound();
        $this->put(route('legal-products.update', ['mou', $archive]), [])
            ->assertNotFound();
    }

    public function test_archive_list_is_searchable_and_paginated_into_compact_cards(): void
    {
        for ($index = 1; $index <= 13; $index++) {
            $this->createArchive('Target Paginasi '.$index, '2030-12-31');
        }

        $this->get(route('legal-products.index', 'pks'))
            ->assertOk()
            ->assertSee('legal-product-toolbar', false)
            ->assertSee('legal-product-card__summary', false)
            ->assertSee('<dialog class="legal-product-edit-modal"', false)
            ->assertSee('data-open-archive-edit="archive-edit-', false)
            ->assertSee('Lihat detail lengkap')
            ->assertSee('Menampilkan 1–12');

        $this->get(route('legal-products.index', ['pks', 'q' => 'Target Paginasi']))
            ->assertOk()
            ->assertSee('13 dokumen')
            ->assertSee('Menampilkan 1–12 dari 13 dokumen')
            ->assertSee('Berikutnya');

        $this->get(route('legal-products.index', ['pks', 'q' => 'Target Paginasi', 'page' => 2]))
            ->assertOk()
            ->assertSee('Menampilkan 13–13 dari 13 dokumen')
            ->assertSee('Target Paginasi 9')
            ->assertDontSee('Target Paginasi 1</h2>');

        $this->get(route('legal-products.index', ['pks', 'q' => 'Target Paginasi 13']))
            ->assertOk()
            ->assertSee('Menampilkan 1–1 dari 1 dokumen')
            ->assertSee('Target Paginasi 13');
    }

    public function test_an_archive_records_party_details_and_multiple_addenda(): void
    {
        Storage::fake('local');
        $this->post(route('legal-products.store', 'mou'), [
            'nama_dokumen' => 'MoU Kolaborasi',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2027-01-01',
            'jangka_waktu' => '1 tahun',
            'stakeholder' => 'Mitra Contoh',
            'unit_pelaksana' => 'Unit Kerja Sama',
            'ruang_lingkup' => 'Kolaborasi riset dan pertukaran data.',
            'penandatangan_pihak_pertama' => 'Pimpinan HLI',
            'penandatangan_pihak_kedua' => 'Pimpinan Mitra',
            'korespondensi_pihak_pertama' => "Sekretariat HLI\nJalan Merdeka\nTelepon: 021-100\nSurel: hli@example.test",
            'korespondensi_pihak_kedua' => "Sekretariat Mitra\nJalan Bersama\nTelepon: 021-200\nSurel: mitra@example.test",
            'document' => UploadedFile::fake()->create('mou-kolaborasi.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('legal-products.index', 'mou'))
            ->assertSessionHasNoErrors();

        $archive = LegalProductArchive::query()->where('nama_dokumen', 'MoU Kolaborasi')->firstOrFail();
        $this->assertSame('Kolaborasi riset dan pertukaran data.', $archive->ruang_lingkup);
        $this->assertSame('Pimpinan HLI', $archive->penandatangan_pihak_pertama);
        $this->assertSame('1 tahun', $archive->jangka_waktu);
        $this->assertSame('1 tahun', $this->createArchive('Arsip tanpa jangka waktu', '2027-01-01')->jangka_waktu);

        $this->post(route('legal-products.addenda.store', ['mou', $archive]), [
            'tanggal_adendum' => '2026-06-15',
            'perubahan' => 'Memperpanjang masa kerja sama selama satu tahun.',
        ])->assertRedirect(route('legal-products.index', 'mou'))
            ->assertSessionHasNoErrors();

        $this->post(route('legal-products.addenda.store', ['mou', $archive]), [
            'tanggal_adendum' => '2026-09-20',
            'perubahan' => 'Menambahkan ruang lingkup pelatihan bersama.',
        ])->assertRedirect(route('legal-products.index', 'mou'))
            ->assertSessionHasNoErrors();

        $this->get(route('legal-products.index', 'mou'))
            ->assertOk()
            ->assertSee('ADA ADENDUM · 2')
            ->assertSee('Memperpanjang masa kerja sama selama satu tahun.')
            ->assertSee('Menambahkan ruang lingkup pelatihan bersama.')
            ->assertSee('Korespondensi Pihak Pertama')
            ->assertSee('Korespondensi Pihak Kedua');

        $this->get(route('isu-strategis.search', ['q' => 'pelatihan bersama']))
            ->assertOk()
            ->assertSee('ADA ADENDUM · 2')
            ->assertSee('Menambahkan ruang lingkup pelatihan bersama.');
    }

    public function test_global_search_finds_activities_by_status_and_legal_product_archives(): void
    {
        IsuStrategis::query()->create([
            'kegiatan' => 'Pencarian Kegiatan Selesai Unik',
            'status' => 'SELESAI',
        ]);
        IsuStrategis::query()->create([
            'kegiatan' => 'Pencarian Kegiatan Proses Unik',
            'status' => 'ON PROGRESS',
        ]);
        IsuStrategis::query()->create([
            'kegiatan' => 'Pencarian Kegiatan Pending Unik',
            'status' => 'PENDING',
        ]);
        $this->createArchive('Dokumen Kerja Sama Pencarian PKS Unik', '2027-01-01', 'pks');
        $this->createArchive('Dokumen Kesepahaman Pencarian MoU Unik', '2027-01-01', 'mou');

        $this->get(route('isu-strategis.search', ['q' => 'SELESAI']))
            ->assertOk()
            ->assertSee('Pencarian Kegiatan Selesai Unik');
        $this->get(route('isu-strategis.search', ['q' => 'ON PROGRESS']))
            ->assertOk()
            ->assertSee('Pencarian Kegiatan Proses Unik');
        $this->get(route('isu-strategis.search', ['q' => 'PENDING']))
            ->assertOk()
            ->assertSee('Pencarian Kegiatan Pending Unik');

        $this->get(route('isu-strategis.search', ['q' => 'PKS']))
            ->assertOk()
            ->assertSee('Dokumen Kerja Sama Pencarian PKS Unik')
            ->assertSee('Buka arsip PKS');
        $this->get(route('isu-strategis.search', ['q' => 'MoU']))
            ->assertOk()
            ->assertSee('Dokumen Kesepahaman Pencarian MoU Unik')
            ->assertSee('Buka arsip MoU');

        $this->get(route('isu-strategis.search', ['q' => 'SELESAI']))
            ->assertDontSee('PKS dan MoU yang Ditemukan')
            ->assertDontSee('Peraturan dan Pasal yang Ditemukan')
            ->assertDontSee('Tidak ada arsip PKS atau MoU yang cocok')
            ->assertDontSee('Tidak ada peraturan atau pasal yang cocok');
    }

    private function createArchive(string $name, string $endDate, string $type = 'pks'): LegalProductArchive
    {
        return LegalProductArchive::query()->create([
            'type' => $type,
            'nama_dokumen' => $name,
            'document_path' => 'produk-hukum/'.$type.'/'.str_replace(' ', '-', strtolower($name)).'.pdf',
            'document_original_name' => str_replace(' ', '-', strtolower($name)).'.pdf',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => $endDate,
            'stakeholder' => 'Stakeholder Uji',
            'unit_pelaksana' => 'Unit Uji',
        ]);
    }
}
