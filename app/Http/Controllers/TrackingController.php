<?php

namespace App\Http\Controllers;

use App\Models\IsuStrategis;
use App\Models\IsuStrategisTracking;
use App\Models\TrackingSlaStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrackingController extends Controller
{
    public function index(): View
    {
        $stages = TrackingSlaStage::query()->orderBy('tahap_ke')->get();

        return view('tracking.index', compact('stages'));
    }

    public function storeStage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_tahap' => ['required', 'string', 'max:255'],
            'sla_hari_kerja' => ['required', 'integer', 'min:0'],
        ]);
        $nextStage = max(
            (int) TrackingSlaStage::query()->max('tahap_ke'),
            (int) IsuStrategisTracking::query()->max('tahap_ke'),
        ) + 1;
        if ($nextStage > 65535) {
            throw ValidationException::withMessages(['nama_tahap' => 'Jumlah tahap SLA sudah mencapai batas.']);
        }

        TrackingSlaStage::query()->create([
            'tahap_ke' => $nextStage,
            ...$data,
        ]);

        return back()->with('success', 'Jenis SLA berhasil ditambahkan.');
    }

    public function updateStage(Request $request, TrackingSlaStage $stage): RedirectResponse
    {
        $data = $request->validate([
            'nama_tahap' => ['required', 'string', 'max:255'],
            'sla_hari_kerja' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($stage, $data): void {
            $stage->update($data);

            $trackingRows = IsuStrategisTracking::query()
                ->where('tahap_ke', $stage->tahap_ke)
                ->where('status', 'ACTIVE')
                ->get();
            foreach ($trackingRows as $tracking) {
                $tracking->update([
                    'nama_tahap' => $data['nama_tahap'],
                    'sla_hari_kerja' => $data['sla_hari_kerja'],
                ]);
            }

            $this->syncIssues($trackingRows->pluck('isu_strategis_no')->unique());
        });

        return back()->with('success', 'Jenis SLA diperbarui untuk kegiatan aktif dan pilihan berikutnya.');
    }

    public function destroyStage(TrackingSlaStage $stage): RedirectResponse
    {
        if (IsuStrategisTracking::query()
            ->where('tahap_ke', $stage->tahap_ke)
            ->where('status', 'ACTIVE')
            ->exists()) {
            throw ValidationException::withMessages([
                'stage' => 'Jenis SLA ini sedang dipakai oleh kegiatan aktif. Selesaikan kegiatan tersebut sebelum menghapusnya.',
            ]);
        }

        $stage->delete();

        return back()->with('success', 'Jenis SLA dihapus. Riwayat SLA yang pernah dipakai tetap tersimpan.');
    }

    public function start(Request $request, IsuStrategis $issue): RedirectResponse
    {
        if ($this->isDone($issue->status)) {
            return back()->with('error', 'Kegiatan yang sudah selesai tidak perlu dibuat tracking baru.')
                ->with('tracking_issue_no', $issue->no);
        }

        $data = $request->validate([
            'sla_stage' => ['required', 'integer', 'exists:tracking_sla_stages,tahap_ke'],
        ]);
        $sla = TrackingSlaStage::query()->where('tahap_ke', $data['sla_stage'])->firstOrFail();

        DB::transaction(function () use ($issue, $sla): void {
            $issue = IsuStrategis::query()->lockForUpdate()->findOrFail($issue->no);
            $issue->tracking()->where('status', 'ACTIVE')->update(['status' => 'REMOVED']);
            IsuStrategisTracking::create([
                'isu_strategis_no' => $issue->no,
                'tahap_ke' => $sla->tahap_ke,
                'nama_tahap' => $sla->nama_tahap,
                'sla_hari_kerja' => $sla->sla_hari_kerja,
                'status' => 'ACTIVE',
                'mulai_pada' => now(),
            ]);
            $issue->update([
                'status' => 'ON PROGRESS',
                'sla' => $sla->nama_tahap,
                'sla_hari_kerja' => $sla->sla_hari_kerja,
                'durasi_aktual' => 0,
            ]);
        });

        return back()->with('success', 'Tracking kegiatan berhasil dimulai.')
            ->with('tracking_issue_no', $issue->no);
    }

    public function complete(IsuStrategisTracking $tracking): RedirectResponse
    {
        session()->flash('tracking_issue_no', $tracking->isu_strategis_no);
        $issue = IsuStrategis::query()->findOrFail($tracking->isu_strategis_no);
        if ($issue->isSlaUnassigned()) {
            return back()->with('error', 'Jenis SLA kegiatan ini belum ditentukan.')
                ->with('tracking_issue_no', $issue->no);
        }

        $result = DB::transaction(function () use ($tracking): bool {
            $activeTracking = IsuStrategisTracking::query()
                ->lockForUpdate()
                ->findOrFail($tracking->id);
            if ($activeTracking->status !== 'ACTIVE') {
                return false;
            }

            $activeTracking->update([
                'status' => 'COMPLETED',
                'selesai_pada' => now(),
            ]);

            IsuStrategisTracking::query()
                ->where('isu_strategis_no', $activeTracking->isu_strategis_no)
                ->where('status', 'PENDING')
                ->update(['status' => 'REMOVED']);

            $issue = IsuStrategis::query()->lockForUpdate()->findOrFail($activeTracking->isu_strategis_no);
            $actualDuration = IsuStrategisTracking::businessDaysElapsed($activeTracking->mulai_pada, $activeTracking->selesai_pada);
            $issue->update([
                'status' => 'SELESAI',
                'progres' => 100,
                'durasi_aktual' => $actualDuration,
            ]);

            return true;
        });

        if (!$result) {
            return back()->with('error', 'SLA ini bukan yang sedang aktif.');
        }

        $issue = IsuStrategis::query()->findOrFail($tracking->isu_strategis_no);

        return back()->with('success', 'SLA selesai dan kegiatan berstatus SELESAI.')
            ->with('tracking_issue_no', $issue->no);
    }

    private function syncIssueSla(IsuStrategis $issue): void
    {
        if ($issue->isSlaUnassigned()) {
            return;
        }

        $active = $issue->tracking->firstWhere('status', 'ACTIVE');
        if ($active) {
            $issue->update([
                'sla' => $active->nama_tahap,
                'sla_hari_kerja' => $active->sla_hari_kerja,
            ]);
        }
    }

    private function isDone(?string $status): bool
    {
        return in_array(strtoupper((string) $status), ['DONE', 'SELESAI', 'COMPLETED'], true);
    }

    private function syncIssues($issueNos): void
    {
        IsuStrategis::query()
            ->whereIn('no', $issueNos)
            ->with('tracking')
            ->get()
            ->each(function (IsuStrategis $issue): void {
                $this->syncIssueSla($issue);
            });
    }

}
