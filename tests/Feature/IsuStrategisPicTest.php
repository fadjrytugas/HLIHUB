<?php

namespace Tests\Feature;

use App\Models\IsuStrategis;
use App\Models\TrackingSlaStage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IsuStrategisPicTest extends TestCase
{
    use DatabaseTransactions;

    public function test_issue_form_saves_and_displays_both_additional_pic_people(): void
    {
        $stage = TrackingSlaStage::query()->firstOrFail();

        $this->get(route('isu-strategis.create'))
            ->assertOk()
            ->assertSee('PIC Pengusul')
            ->assertSee('PIC HHI/HLI')
            ->assertSee('Keterangan Update')
            ->assertSee('name="keterangan_update"', false);

        $this->post(route('isu-strategis.store'), [
            'unit_lembaga_pengusul' => 'Unit Uji PIC',
            'pic_orang_unit_lembaga_pengusul' => 'Nama PIC Pengusul',
            'stakeholder' => 'Stakeholder Uji PIC',
            'klasifikasi_strategis' => 'Klasifikasi Uji PIC',
            'kegiatan' => 'Kegiatan Uji Simpan PIC',
            'pic' => 'Tim PIC',
            'pic_orang_pic' => 'Nama Orang PIC',
            'tanggal_masuk' => '2026-10-16',
            'deadline' => '2026-10-30',
            'progres' => 10,
            'sla_stage' => $stage->tahap_ke,
        ])->assertRedirect('/');

        $issue = IsuStrategis::query()->where('kegiatan', 'Kegiatan Uji Simpan PIC')->firstOrFail();
        $this->assertSame('Nama PIC Pengusul', $issue->pic_orang_unit_lembaga_pengusul);
        $this->assertSame('Nama Orang PIC', $issue->pic_orang_pic);

        $this->get(route('isu-strategis.search', ['q' => 'Nama PIC Pengusul']))
            ->assertOk()
            ->assertSee('Nama PIC Pengusul')
            ->assertSee('Nama Orang PIC');

        $this->get(route('isu-strategis.search', ['q' => 'Nama Orang PIC']))
            ->assertOk()
            ->assertSee('Nama PIC Pengusul')
            ->assertSee('Nama Orang PIC');
    }

    public function test_tracking_page_search_shows_only_matching_issues(): void
    {
        IsuStrategis::query()->create(['kegiatan' => 'Kegiatan Cocok Uji Pencarian']);
        IsuStrategis::query()->create(['kegiatan' => 'Kegiatan Lain Uji Pencarian']);

        $this->withSession(['dashboard_authenticated' => true])
            ->get(route('isu-strategis.data', ['q' => 'Kegiatan Cocok Uji']))
            ->assertOk()
            ->assertSee('Kegiatan Cocok Uji Pencarian')
            ->assertDontSee('Kegiatan Lain Uji Pencarian')
            ->assertSee('name="q" type="search" value="Kegiatan Cocok Uji"', false);

        $this->get(route('isu-strategis.data', ['q' => '']))
            ->assertOk()
            ->assertSee('Seluruh Isu Strategis')
            ->assertDontSee('Hasil Pencarian')
            ->assertSee('name="q" type="search" value=""', false)
            ->assertViewHas('issues', fn ($issues): bool => $issues->total() === IsuStrategis::query()->count()
                && $issues->currentPage() === 1);
    }

    public function test_issue_update_saves_and_displays_the_latest_update_note(): void
    {
        $issue = IsuStrategis::query()->create([
            'unit_lembaga_pengusul' => 'Unit Catatan Update',
            'stakeholder' => 'Stakeholder Catatan Update',
            'klasifikasi_strategis' => 'Klasifikasi Catatan Update',
            'kegiatan' => 'Kegiatan Catatan Update',
            'tanggal_masuk' => '2026-10-16',
            'deadline' => '2026-10-30',
            'progres' => 10,
        ]);

        $this->withSession(['dashboard_authenticated' => true])
            ->put(route('isu-strategis.update', $issue), [
                'unit_lembaga_pengusul' => 'Unit Catatan Update',
                'stakeholder' => 'Stakeholder Catatan Update',
                'klasifikasi_strategis' => 'Klasifikasi Catatan Update',
                'kegiatan' => 'Kegiatan Catatan Update',
                'tanggal_masuk' => '2026-10-16',
                'deadline' => '2026-10-30',
                'terakhir_di_update' => '2026-10-17T10:00',
                'keterangan_update' => 'Progres diperbarui menjadi tahap koordinasi dengan stakeholder.',
                'progres' => 25,
            ])
            ->assertRedirect('/');

        $this->assertSame(
            'Progres diperbarui menjadi tahap koordinasi dengan stakeholder.',
            $issue->fresh()->keterangan_update,
        );

        $this->withSession(['dashboard_authenticated' => true])
            ->get(route('isu-strategis.data', ['q' => 'Kegiatan Catatan Update']))
            ->assertOk()
            ->assertSee('KETERANGAN UPDATE')
            ->assertSee('Progres diperbarui menjadi tahap koordinasi dengan stakeholder.');
    }
}
