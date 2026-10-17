<?php

namespace Tests\Feature;

use App\Models\IsuStrategis;
use App\Models\IsuStrategisTracking;
use App\Models\LegalProductArchive;
use App\Models\Stakeholder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_notifies_for_overdue_sla_upcoming_deadlines_and_birthdays(): void
    {
        Carbon::setTestNow('2026-10-12 12:00:00');

        $overdueIssue = IsuStrategis::query()->create([
            'kegiatan' => 'Kegiatan SLA Terlambat',
            'status' => 'ON PROGRESS',
        ]);
        IsuStrategisTracking::query()->create([
            'isu_strategis_no' => $overdueIssue->no,
            'tahap_ke' => 1,
            'nama_tahap' => 'Verifikasi Dokumen',
            'sla_hari_kerja' => 5,
            'status' => 'ACTIVE',
            'mulai_pada' => '2026-10-02 00:00:00',
        ]);

        $slaDueTodayIssue = IsuStrategis::query()->create([
            'kegiatan' => 'Kegiatan SLA Jatuh Tempo Hari Ini',
            'status' => 'ON PROGRESS',
        ]);
        IsuStrategisTracking::query()->create([
            'isu_strategis_no' => $slaDueTodayIssue->no,
            'tahap_ke' => 1,
            'nama_tahap' => 'Verifikasi Dokumen',
            'sla_hari_kerja' => 5,
            'status' => 'ACTIVE',
            'mulai_pada' => '2026-10-05 00:00:00',
        ]);

        IsuStrategis::query()->create([
            'kegiatan' => 'Kegiatan Deadline H-5',
            'status' => 'ON PROGRESS',
            'deadline' => '2026-10-17',
        ]);
        IsuStrategis::query()->create([
            'kegiatan' => 'Kegiatan Deadline H-4',
            'status' => 'PENDING',
            'deadline' => '2026-10-16',
        ]);
        IsuStrategis::query()->create([
            'kegiatan' => 'Kegiatan Deadline H-3',
            'status' => 'ON PROGRESS',
            'deadline' => '2026-10-15',
        ]);
        $deadlineOutsideWindow = IsuStrategis::query()->create([
            'kegiatan' => 'Kegiatan Deadline H-6',
            'status' => 'ON PROGRESS',
            'deadline' => '2026-10-18',
        ]);
        LegalProductArchive::query()->create([
            'type' => 'pks',
            'nama_dokumen' => 'PKS Berakhir dalam Tiga Bulan',
            'document_path' => 'produk-hukum/pks/tiga-bulan.pdf',
            'document_original_name' => 'tiga-bulan.pdf',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2027-01-12',
            'stakeholder' => 'Mitra PKS',
            'unit_pelaksana' => 'Unit Kerja Sama',
        ]);
        LegalProductArchive::query()->create([
            'type' => 'mou',
            'nama_dokumen' => 'MoU Sudah Berakhir',
            'document_path' => 'produk-hukum/mou/sudah-berakhir.pdf',
            'document_original_name' => 'sudah-berakhir.pdf',
            'tanggal_mulai' => '2025-01-01',
            'tanggal_berakhir' => '2025-12-31',
            'stakeholder' => 'Mitra MoU',
            'unit_pelaksana' => 'Unit Kerja Sama',
        ]);

        $stakeholder = Stakeholder::query()->create([
            'nama' => 'Instansi Ulang Tahun',
            'prioritas' => '2ND PRIORITY',
        ]);
        $stakeholder->members()->create([
            'nama_lengkap' => 'Anggota Ulang Tahun H-2',
            'jabatan' => 'Direktur',
            'tanggal_lahir' => '1980-10-14',
        ]);
        $stakeholder->members()->create([
            'nama_lengkap' => 'Anggota Ulang Tahun H-3',
            'tanggal_lahir' => '1980-10-15',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                'Anggota Ulang Tahun H-2',
                'Kegiatan Deadline H-3',
                'Kegiatan SLA Terlambat',
                'Kegiatan Deadline H-4',
                'Kegiatan Deadline H-5',
            ])
            ->assertSee('SLA terlewati')
            ->assertSee('Kegiatan SLA Terlambat')
            ->assertSee('Deadline H-5')
            ->assertSee('Kegiatan Deadline H-5')
            ->assertSee('Deadline H-3')
            ->assertSee('Kegiatan Deadline H-3')
            ->assertSee('Deadline H-4')
            ->assertSee('Kegiatan Deadline H-4')
            ->assertSee('Ulang tahun H-2')
            ->assertSee('Anggota Ulang Tahun H-2')
            ->assertSee('PKS akan berakhir (H-3 bulan)')
            ->assertSee('PKS Berakhir dalam Tiga Bulan')
            ->assertSee('H-3 bulan')
            ->assertSee('MoU sudah berakhir')
            ->assertSee('MoU Sudah Berakhir')
            ->assertDontSee('Kegiatan SLA Jatuh Tempo Hari Ini — SLA Verifikasi Dokumen')
            ->assertDontSee('data-notification-id="deadline:'.$deadlineOutsideWindow->no.':2026-10-18"', false)
            ->assertDontSee('Anggota Ulang Tahun H-3');
    }
}
