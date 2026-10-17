<?php

namespace App\Http\Controllers;

use App\Models\DasarHukumPeraturan;
use App\Models\IsuStrategis;
use App\Models\LegalProductArchive;
use App\Models\StakeholderMember;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $allIssues = IsuStrategis::query()->with('tracking')->orderBy('no')->get();
        $selectedPic = trim((string) $request->query('pic', ''));
        $issues = $selectedPic === ''
            ? $allIssues
            : $allIssues->filter(fn (IsuStrategis $issue): bool => trim((string) $issue->pic) === $selectedPic)->values();
        $totalIssues = $issues->count();
        $doneIssues = $issues->filter(fn (IsuStrategis $issue): bool => $this->isDone($issue->status))->count();
        $progressIssues = $issues->filter(fn (IsuStrategis $issue): bool => $this->isProgress($issue->status))->count();
        $pendingIssues = max(0, $totalIssues - $doneIssues - $progressIssues);
        $priorityIssues = $issues
            ->filter(fn (IsuStrategis $issue): bool => $this->isProgress($issue->status))
            ->sort(function (IsuStrategis $left, IsuStrategis $right): int {
                $leftDeadline = $left->deadline?->timestamp ?? PHP_INT_MAX;
                $rightDeadline = $right->deadline?->timestamp ?? PHP_INT_MAX;

                if ($leftDeadline !== $rightDeadline) {
                    return $leftDeadline <=> $rightDeadline;
                }

                return ($left->tanggal_masuk?->timestamp ?? PHP_INT_MAX)
                    <=> ($right->tanggal_masuk?->timestamp ?? PHP_INT_MAX);
            })
            ->values();
        $period = $request->integer('period', 6);
        $period = in_array($period, [6, 12], true) ? $period : 6;
        $trend = $this->buildTrend($issues, $period);
        $notifications = $this->buildNotifications($issues);

        return view('welcome', [
            'issues' => $issues,
            'priorityIssues' => $priorityIssues,
            'notifications' => $notifications,
            'metrics' => [
                'total' => $totalIssues,
                'done' => $doneIssues,
                'progress' => $progressIssues,
                'pending' => $pendingIssues,
                'units' => $this->uniqueCount($issues, 'unit_lembaga_pengusul'),
                'stakeholders' => $this->uniqueCount($issues, 'stakeholder'),
            ],
            'statusPercentages' => [
                'done' => $this->percentage($doneIssues, $totalIssues),
                'progress' => $this->percentage($progressIssues, $totalIssues),
                'pending' => $this->percentage($pendingIssues, $totalIssues),
            ],
            'unitBreakdown' => $this->countBy($issues, 'unit_lembaga_pengusul'),
            'classificationBreakdown' => $this->countBy($issues, 'klasifikasi_strategis'),
            'stakeholderBreakdown' => $this->countBy($issues, 'stakeholder')->take(25),
            'trend' => $trend,
            'trendPeriod' => $period,
            'selectedPic' => $selectedPic,
            'searchTerm' => '',
            'picOptions' => $allIssues->pluck('pic')
                ->filter(fn (?string $pic): bool => trim((string) $pic) !== '')
                ->map(fn (string $pic): string => trim($pic))
                ->unique()
                ->sort()
                ->values(),
        ]);
    }

    private function buildNotifications(Collection $issues): Collection
    {
        $today = Carbon::today();
        $notifications = collect();

        foreach ($issues as $issue) {
            if ($this->isDone($issue->status)) {
                continue;
            }

            $activeTracking = $issue->tracking->firstWhere('status', 'ACTIVE');
            $slaDeadline = $activeTracking?->deadline_pada;
            if (!$issue->isSlaUnassigned() && $slaDeadline && $slaDeadline->lt($today)) {
                $notifications->push([
                    'type' => 'sla',
                    'id' => 'sla:'.$issue->no.':'.$slaDeadline->toDateString(),
                    'sort_at' => $slaDeadline->toDateString(),
                    'title' => 'SLA terlewati',
                    'subject' => $issue->kegiatan,
                    'description' => 'Tahap '.$activeTracking->nama_tahap.' telah melewati batas SLA.',
                    'date_label' => 'Terlambat sejak',
                    'date' => $slaDeadline->format('d M Y'),
                    'url' => route('isu-strategis.edit', $issue->no),
                ]);
            }

            $daysUntilDeadline = $issue->deadline
                ? (int) $today->diffInDays($issue->deadline, false)
                : null;
            if ($daysUntilDeadline !== null && $daysUntilDeadline >= 0 && $daysUntilDeadline <= 5) {
                $notifications->push([
                    'type' => 'deadline',
                    'id' => 'deadline:'.$issue->no.':'.$issue->deadline->toDateString(),
                    'sort_at' => $issue->deadline->toDateString(),
                    'title' => $daysUntilDeadline === 0 ? 'Deadline hari ini' : 'Deadline H-'.$daysUntilDeadline,
                    'subject' => $issue->kegiatan,
                    'description' => $daysUntilDeadline === 0
                        ? 'Kegiatan jatuh tempo hari ini.'
                        : 'Kegiatan akan jatuh tempo dalam '.$daysUntilDeadline.' hari.',
                    'days_remaining' => $daysUntilDeadline,
                    'date_label' => 'Deadline',
                    'date' => $issue->deadline->format('d M Y'),
                    'url' => route('isu-strategis.edit', $issue->no),
                ]);
            }
        }

        LegalProductArchive::query()
            ->whereDate('tanggal_berakhir', '<=', $today->copy()->addMonthsNoOverflow(3))
            ->get()
            ->filter(fn (LegalProductArchive $archive): bool => $archive->isExpired($today)
                || $archive->isExpiringWithinThreeMonths($today))
            ->each(function (LegalProductArchive $archive) use ($notifications, $today): void {
                $daysRemaining = (int) $today->diffInDays($archive->tanggal_berakhir, false);
                $type = $archive->type === 'mou' ? 'MoU' : 'PKS';
                $isExpired = $archive->isExpired($today);
                $description = match (true) {
                    $isExpired => 'Periode '.$type.' berakhir pada '.$archive->tanggal_berakhir->format('d M Y').'.',
                    $daysRemaining > 0 => 'Periode '.$type.' akan berakhir dalam '.$daysRemaining.' hari.',
                    $daysRemaining === 0 => 'Periode '.$type.' berakhir hari ini.',
                    default => 'Periode '.$type.' berakhir '.abs($daysRemaining).' hari yang lalu.',
                };

                $notifications->push([
                    'type' => 'legal-product',
                    'expiry_status' => $isExpired ? 'expired' : 'expiring',
                    'document_type' => $type,
                    'id' => 'legal-product:'.$archive->id.':'.$archive->tanggal_berakhir->toDateString(),
                    'sort_at' => $archive->tanggal_berakhir->toDateString(),
                    'title' => $isExpired ? $type.' sudah berakhir' : $type.' akan berakhir (H-3 bulan)',
                    'subject' => $archive->nama_dokumen,
                    'description' => $description.' Stakeholder: '.$archive->stakeholder.' · Unit: '.$archive->unit_pelaksana,
                    'date_label' => 'Berakhir',
                    'date' => $archive->tanggal_berakhir->format('d M Y'),
                    'url' => route('legal-products.index', $archive->type).'#archive-'.$archive->id,
                ]);
            });

        $birthdayDate = $today->copy()->addDays(2);
        StakeholderMember::query()
            ->with('stakeholder')
            ->whereMonth('tanggal_lahir', $birthdayDate->month)
            ->whereDay('tanggal_lahir', $birthdayDate->day)
            ->get()
            ->each(function (StakeholderMember $member) use ($notifications, $birthdayDate): void {
                $stakeholder = $member->stakeholder?->nama;
                $notifications->push([
                    'type' => 'birthday',
                    'id' => 'birthday:'.$member->id.':'.$birthdayDate->toDateString(),
                    'sort_at' => $birthdayDate->toDateString(),
                    'title' => 'Ulang tahun H-2',
                    'subject' => $member->nama_lengkap,
                    'description' => implode(' · ', array_filter([$member->jabatan, $stakeholder])),
                    'date_label' => 'Ulang tahun',
                    'date' => $birthdayDate->format('d M Y'),
                    'url' => $member->stakeholder
                        ? route('stakeholders.show', $member->stakeholder)
                        : route('stakeholders.index'),
                ]);
            });

        return $notifications->sort(function (array $left, array $right) use ($today): int {
            $leftDate = Carbon::parse($left['sort_at']);
            $rightDate = Carbon::parse($right['sort_at']);
            $leftDistance = abs((int) $today->diffInDays($leftDate, false));
            $rightDistance = abs((int) $today->diffInDays($rightDate, false));

            if ($leftDistance !== $rightDistance) {
                return $leftDistance <=> $rightDistance;
            }

            if ($leftDate->isFuture() !== $rightDate->isFuture()) {
                return $leftDate->isFuture() ? -1 : 1;
            }

            return $leftDate->isFuture()
                ? $leftDate <=> $rightDate
                : $rightDate <=> $leftDate;
        })->values();
    }

    public function data(Request $request): View
    {
        $searchTerm = trim((string) $request->query('q', ''));
        return view('isu-strategis.data', [
            'issues' => IsuStrategis::query()
                ->with('tracking')
                ->when($searchTerm !== '', function ($query) use ($searchTerm): void {
                    $query->where(function ($query) use ($searchTerm): void {
                        foreach ([
                            'no',
                            'unit_lembaga_pengusul',
                            'pic_orang_unit_lembaga_pengusul',
                            'stakeholder',
                            'klasifikasi_strategis',
                            'kegiatan',
                            'deskripsi_kegiatan',
                            'rincian_kegiatan',
                            'goals',
                            'progres',
                            'status',
                            'pic',
                            'pic_orang_pic',
                            'sla',
                            'keterangan_update',
                            'hambatan_kendala',
                            'dampak',
                        ] as $field) {
                            $query->orWhere($field, 'like', '%'.$searchTerm.'%');
                        }
                    });
                })
                ->orderBy('no')
                ->paginate(10)
                ->withQueryString(),
            'slaOptions' => \App\Models\TrackingSlaStage::query()->orderBy('nama_tahap')->get(),
            'isSearch' => false,
            'searchTerm' => $searchTerm,
        ]);
    }

    public function search(Request $request): View
    {
        $searchTerm = trim((string) $request->query('q', ''));
        $regulations = DasarHukumPeraturan::query()
            ->with(['pasal' => function ($query) use ($searchTerm): void {
                $query->where(function ($query) use ($searchTerm): void {
                    $query->where('nomor_pasal', 'like', '%'.$searchTerm.'%')
                        ->orWhere('penjelasan', 'like', '%'.$searchTerm.'%');
                });
            }])
            ->where(function ($query) use ($searchTerm): void {
                if ($searchTerm === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('nama_peraturan', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('pasal', function ($query) use ($searchTerm): void {
                        $query->where('nomor_pasal', 'like', '%'.$searchTerm.'%')
                            ->orWhere('penjelasan', 'like', '%'.$searchTerm.'%');
                    });
            })
            ->orderBy('nama_peraturan')
            ->paginate(10, ['*'], 'regulation_page')
            ->withQueryString();

        $legalProductArchives = LegalProductArchive::query()
            ->with('addenda')
            ->when($searchTerm === '', function ($query): void {
                $query->whereRaw('1 = 0');
            })
            ->when($searchTerm !== '', function ($query) use ($searchTerm): void {
                $query->where(function ($query) use ($searchTerm): void {
                    foreach ([
                        'type',
                        'nama_dokumen',
                        'document_original_name',
                        'stakeholder',
                        'unit_pelaksana',
                        'ruang_lingkup',
                        'penandatangan_pihak_pertama',
                        'penandatangan_pihak_kedua',
                        'korespondensi_pihak_pertama',
                        'korespondensi_pihak_kedua',
                        'tanggal_mulai',
                        'tanggal_berakhir',
                        'jangka_waktu',
                    ] as $field) {
                        $query->orWhere($field, 'like', '%'.$searchTerm.'%');
                    }
                    $query->orWhereHas('addenda', function ($addendumQuery) use ($searchTerm): void {
                        $addendumQuery->where('perubahan', 'like', '%'.$searchTerm.'%')
                            ->orWhere('tanggal_adendum', 'like', '%'.$searchTerm.'%');
                    });
                });
            })
            ->orderBy('tanggal_berakhir')
            ->orderBy('nama_dokumen')
            ->paginate(10, ['*'], 'archive_page')
            ->withQueryString();

        $issues = IsuStrategis::query()
            ->with('tracking')
            ->when($searchTerm !== '', function ($query) use ($searchTerm): void {
                $query->where(function ($query) use ($searchTerm): void {
                    foreach ([
                        'no',
                        'unit_lembaga_pengusul',
                        'pic_orang_unit_lembaga_pengusul',
                        'stakeholder',
                        'klasifikasi_strategis',
                        'kegiatan',
                        'deskripsi_kegiatan',
                        'rincian_kegiatan',
                        'goals',
                        'progres',
                        'status',
                        'pic',
                        'pic_orang_pic',
                        'sla',
                        'keterangan_update',
                        'hambatan_kendala',
                        'dampak',
                    ] as $field) {
                        $query->orWhere($field, 'like', '%'.$searchTerm.'%');
                    }
                });
            })
            ->orderByDesc('no')
            ->paginate(10)
            ->withQueryString();

        return view('isu-strategis.data', [
            'issues' => $issues,
            'slaOptions' => \App\Models\TrackingSlaStage::query()->orderBy('nama_tahap')->get(),
            'isSearch' => true,
            'searchTerm' => $searchTerm,
            'legalRegulations' => $regulations,
            'legalProductArchives' => $legalProductArchives,
        ]);
    }

    private function isDone(?string $status): bool
    {
        return in_array(strtoupper((string) $status), ['DONE', 'SELESAI', 'COMPLETED'], true);
    }

    private function isProgress(?string $status): bool
    {
        return in_array(strtoupper((string) $status), ['ON PROGRESS', 'PROGRESS', 'IN PROGRESS'], true);
    }

    private function percentage(int $value, int $total): float
    {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0;
    }

    private function uniqueCount(Collection $issues, string $field): int
    {
        if (in_array($field, ['stakeholder', 'unit_lembaga_pengusul'], true)) {
            return $this->splitValues($issues, $field)->unique('key')->count();
        }

        return $issues->pluck($field)->filter()->unique()->count();
    }

    private function countBy(Collection $issues, string $field): Collection
    {
        if (in_array($field, ['stakeholder', 'unit_lembaga_pengusul'], true)) {
            $values = $this->splitValues($issues, $field);

            return $values
                ->countBy('key')
                ->sortDesc()
                ->map(fn (int $count, string $key): array => [
                    'label' => $values->firstWhere('key', $key)['label'],
                    'count' => $count,
                ])
                ->values();
        }

        return $issues->pluck($field)
            ->filter()
            ->countBy()
            ->sortDesc()
            ->map(fn (int $count, string $label): array => ['label' => $label, 'count' => $count])
            ->values();
    }

    private function splitValues(Collection $issues, string $field): Collection
    {
        return $issues->pluck($field)
            ->filter()
            ->flatMap(function (string $value) use ($field): array {
                $parts = preg_split('/\s*(?:,|\/|&|\bdan\b)\s*/i', $value);

                return collect($parts)
                    ->map(fn (string $part): string => trim($part))
                    ->filter()
                    ->map(function (string $part) use ($field): array {
                        $label = match ($field) {
                            'stakeholder' => $this->normalizeStakeholder($part),
                            'unit_lembaga_pengusul' => $this->normalizeUnit($part),
                            default => $part,
                        };

                        return [
                            'key' => mb_strtolower($label),
                            'label' => $label,
                        ];
                    })
                    ->all();
            })
            ->values();
    }

    private function normalizeStakeholder(string $value): string
    {
        return preg_match('/^kemenhub\b/i', $value) === 1 ? 'Kemenhub' : $value;
    }

    private function normalizeUnit(string $value): string
    {
        return trim((string) preg_replace('/\s*\([^)]*\)\s*$/', '', $value));
    }

    private function buildTrend(Collection $issues, int $period): array
    {
        $labels = [];
        $totalValues = [];
        $doneValues = [];
        $current = Carbon::now()->startOfMonth()->subMonths($period - 1);

        for ($index = 0; $index < $period; $index++) {
            $monthEnd = $current->copy()->endOfMonth();
            $datedIssues = $issues->filter(
                fn (IsuStrategis $issue): bool => $issue->terakhir_di_update !== null
                    && $issue->terakhir_di_update->lessThanOrEqualTo($monthEnd)
            );

            $labels[] = $current->translatedFormat('M');
            $totalValues[] = $datedIssues->count();
            $doneValues[] = $datedIssues->filter(fn (IsuStrategis $issue): bool => $this->isDone($issue->status))->count();
            $current->addMonth();
        }

        $maximum = max(1, ...$totalValues, ...$doneValues);
        $toPoints = static function (array $values) use ($maximum, $period): string {
            $step = $period > 1 ? 600 / ($period - 1) : 600;

            return collect($values)->map(
                fn (int $value, int $index): string => round($index * $step, 2).','.(220 - round(($value / $maximum) * 190))
            )->implode(' ');
        };

        return [
            'labels' => $labels,
            'totalPoints' => $toPoints($totalValues),
            'donePoints' => $toPoints($doneValues),
        ];
    }
}
