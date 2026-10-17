<?php

namespace Tests\Unit;

use App\Models\IsuStrategis;
use App\Models\IsuStrategisTracking;
use Carbon\Carbon;
use Tests\TestCase;

class IsuStrategisTrackingTest extends TestCase
{
    public function test_actual_duration_counts_weekdays_after_sla_start(): void
    {
        $startedAt = Carbon::parse('2026-10-12 09:00:00');
        $finishedAt = Carbon::parse('2026-10-17 16:00:00');

        $this->assertSame(4, IsuStrategisTracking::businessDaysElapsed($startedAt, $finishedAt));
    }

    public function test_actual_duration_skips_weekend_days(): void
    {
        $startedAt = Carbon::parse('2026-10-16 09:00:00');
        $finishedAt = Carbon::parse('2026-10-19 16:00:00');

        $this->assertSame(1, IsuStrategisTracking::businessDaysElapsed($startedAt, $finishedAt));
    }

    public function test_actual_duration_stays_blank_for_unassigned_issues_one_to_fifty(): void
    {
        Carbon::setTestNow('2026-10-19 12:00:00');
        $issue = new IsuStrategis([
            'sla' => null,
            'sla_hari_kerja' => null,
            'durasi_aktual' => null,
        ]);
        $issue->no = 1;
        $issue->setRelation('tracking', collect([
            new IsuStrategisTracking([
                'status' => 'ACTIVE',
                'mulai_pada' => '2026-10-12 09:00:00',
            ]),
        ]));

        $this->assertTrue($issue->isSlaUnassigned());
        $this->assertNull($issue->durasi_aktual);

        $issue->sla = 'Verifikasi Dokumen';
        $issue->sla_hari_kerja = 5;

        $this->assertFalse($issue->isSlaUnassigned());
        $this->assertSame(5, $issue->durasi_aktual);

        Carbon::setTestNow();
    }
}
