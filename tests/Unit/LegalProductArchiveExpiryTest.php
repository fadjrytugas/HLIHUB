<?php

namespace Tests\Unit;

use App\Models\LegalProductArchive;
use Carbon\Carbon;
use Tests\TestCase;

class LegalProductArchiveExpiryTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_expiry_status_distinguishes_expired_archives_from_the_three_month_warning_window(): void
    {
        Carbon::setTestNow('2026-10-13 12:00:00');

        $this->assertTrue($this->archiveEnding('2027-01-13')->isExpiringWithinThreeMonths());
        $this->assertTrue($this->archiveEnding('2026-10-13')->isExpiringWithinThreeMonths());
        $this->assertFalse($this->archiveEnding('2027-01-14')->isExpiringWithinThreeMonths());
        $this->assertFalse($this->archiveEnding('2026-10-13')->isExpired());

        $this->assertTrue($this->archiveEnding('2026-10-12')->isExpired());
        $this->assertFalse($this->archiveEnding('2026-10-12')->isExpiringWithinThreeMonths());
        $this->assertTrue($this->archiveEnding('2020-01-01')->isExpired());
    }

    public function test_legacy_archive_duration_is_derived_from_start_and_end_dates(): void
    {
        $archive = new LegalProductArchive([
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2028-04-03',
        ]);

        $this->assertSame('2 tahun 3 bulan 2 hari', $archive->jangka_waktu);
    }

    private function archiveEnding(string $date): LegalProductArchive
    {
        return new LegalProductArchive(['tanggal_berakhir' => $date]);
    }
}
