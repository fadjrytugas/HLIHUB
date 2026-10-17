<?php

namespace App\Http\Controllers;

use App\Models\IsuStrategis;
use App\Models\IsuStrategisTracking;
use App\Models\TrackingSlaStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class IsuStrategisController extends Controller
{
    public function create(): View
    {
        return view('isu-strategis.create', [
            'issue' => null,
            'slaOptions' => TrackingSlaStage::query()->orderBy('nama_tahap')->get(),
        ]);
    }

    public function edit(IsuStrategis $isuStrategi): View
    {
        return view('isu-strategis.create', [
            'issue' => $isuStrategi->load('tracking'),
            'slaOptions' => TrackingSlaStage::query()->orderBy('nama_tahap')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request, true);
        $sla = TrackingSlaStage::query()->where('tahap_ke', $data['sla_stage'])->firstOrFail();
        unset($data['sla_stage']);

        DB::transaction(function () use ($data, $sla): void {
            $issue = IsuStrategis::create([
                ...$data,
                'status' => 'ON PROGRESS',
                'sla' => $sla->nama_tahap,
                'sla_hari_kerja' => $sla->sla_hari_kerja,
                'durasi_aktual' => 0,
            ]);

            $startedAt = $issue->tanggal_masuk->copy()->startOfDay();
            IsuStrategisTracking::create([
                'isu_strategis_no' => $issue->no,
                'tahap_ke' => $sla->tahap_ke,
                'nama_tahap' => $sla->nama_tahap,
                'sla_hari_kerja' => $sla->sla_hari_kerja,
                'status' => 'ACTIVE',
                'mulai_pada' => $startedAt,
            ]);
        });

        return redirect('/')->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, IsuStrategis $isuStrategi): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $isuStrategi->update($validated);

        return redirect('/')->with('success', 'Data berhasil diperbarui.');
    }

    private function validatedData(Request $request, bool $creating = false): array
    {
        $rules = [
            'unit_lembaga_pengusul' => ['required', 'string', 'max:255'],
            'pic_orang_unit_lembaga_pengusul' => ['nullable', 'string', 'max:255'],
            'stakeholder' => ['required', 'string', 'max:255'],
            'klasifikasi_strategis' => ['required', 'string', 'max:255'],
            'kegiatan' => ['required', 'string'],
            'deskripsi_kegiatan' => ['nullable', 'string'],
            'rincian_kegiatan' => ['nullable', 'string'],
            'goals' => ['nullable', 'string'],
            'pic' => ['nullable', 'string', 'max:255'],
            'pic_orang_pic' => ['nullable', 'string', 'max:255'],
            'hambatan_kendala' => ['nullable', 'string'],
            'dampak' => ['nullable', 'string'],
            'progres' => ['required', 'integer', 'between:0,100'],
            'tanggal_masuk' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after_or_equal:tanggal_masuk'],
            'terakhir_di_update' => ['nullable', 'date'],
            'keterangan_update' => ['nullable', 'string', 'max:5000'],
        ];
        if ($creating) {
            $rules['sla_stage'] = ['required', 'integer', 'exists:tracking_sla_stages,tahap_ke'];
        }

        return $request->validate($rules);
    }
}
