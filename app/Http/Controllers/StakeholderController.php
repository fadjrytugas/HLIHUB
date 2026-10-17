<?php

namespace App\Http\Controllers;

use App\Models\Stakeholder;
use App\Models\StakeholderMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StakeholderController extends Controller
{
    public function index(): View
    {
        $topPriorityStakeholders = Stakeholder::query()
            ->withCount('members')
            ->where('prioritas', 'TOP PRIORITY')
            ->orderBy('nama')
            ->paginate(12, ['*'], 'top_page');

        $secondPriorityStakeholders = Stakeholder::query()
            ->withCount('members')
            ->where('prioritas', '2ND PRIORITY')
            ->orderBy('nama')
            ->paginate(12, ['*'], 'second_page');

        $activePriority = request()->query('priority');
        if (! in_array($activePriority, ['TOP PRIORITY', '2ND PRIORITY'], true)) {
            $activePriority = $topPriorityStakeholders->total() > 0 ? 'TOP PRIORITY' : '2ND PRIORITY';
        }

        return view('stakeholder.index', [
            'topPriorityStakeholders' => $topPriorityStakeholders,
            'secondPriorityStakeholders' => $secondPriorityStakeholders,
            'stakeholderTotal' => $topPriorityStakeholders->total() + $secondPriorityStakeholders->total(),
            'activePriority' => $activePriority,
        ]);
    }

    public function show(Stakeholder $stakeholder): View
    {
        $stakeholder->load(['members' => fn ($query) => $query->orderBy('nama_lengkap')]);

        return view('stakeholder.show', compact('stakeholder'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->stakeholderRules());
        $stakeholder = Stakeholder::query()->create($data);

        return redirect()->route('stakeholders.show', $stakeholder)
            ->with('success', 'Stakeholder berhasil ditambahkan. Lengkapi anggota pada halaman detail.');
    }

    public function update(Request $request, Stakeholder $stakeholder): RedirectResponse
    {
        $stakeholder->update($request->validate($this->stakeholderRules($stakeholder)));

        return back()->with('success', 'Data stakeholder berhasil diperbarui.');
    }

    public function destroy(Stakeholder $stakeholder): RedirectResponse
    {
        $photoPaths = $stakeholder->members()->whereNotNull('foto_path')->pluck('foto_path');
        $stakeholder->delete();
        foreach ($photoPaths as $photoPath) {
            Storage::delete($photoPath);
        }

        return redirect()->route('stakeholders.index')->with('success', 'Stakeholder dan seluruh anggotanya berhasil dihapus.');
    }

    public function storeMember(Request $request, Stakeholder $stakeholder): RedirectResponse
    {
        $data = $request->validate($this->memberRules());
        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('stakeholder-members');
        }
        unset($data['foto']);

        $stakeholder->members()->create($data);

        return back()->with('success', 'Anggota stakeholder berhasil ditambahkan.');
    }

    public function updateMember(
        Request $request,
        Stakeholder $stakeholder,
        StakeholderMember $member
    ): RedirectResponse {
        $this->ensureMemberBelongsToStakeholder($stakeholder, $member);
        $data = $request->validate($this->memberRules());
        $oldPhotoPath = $member->foto_path;
        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('stakeholder-members');
        }
        unset($data['foto']);
        $member->update($data);

        if ($member->foto_path !== $oldPhotoPath && $oldPhotoPath) {
            Storage::delete($oldPhotoPath);
        }

        return back()->with('success', 'Data anggota stakeholder berhasil diperbarui.');
    }

    public function destroyMember(Stakeholder $stakeholder, StakeholderMember $member): RedirectResponse
    {
        $this->ensureMemberBelongsToStakeholder($stakeholder, $member);
        $photoPath = $member->foto_path;
        $member->delete();
        if ($photoPath) {
            Storage::delete($photoPath);
        }

        return back()->with('success', 'Anggota stakeholder berhasil dihapus.');
    }

    public function photo(Stakeholder $stakeholder, StakeholderMember $member): StreamedResponse
    {
        $this->ensureMemberBelongsToStakeholder($stakeholder, $member);
        abort_unless($member->foto_path && Storage::exists($member->foto_path), 404);

        return Storage::response($member->foto_path, null, ['X-Content-Type-Options' => 'nosniff'], 'inline');
    }

    private function stakeholderRules(?Stakeholder $stakeholder = null): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stakeholders', 'nama')->ignore($stakeholder?->id),
            ],
            'prioritas' => ['required', Rule::in(['TOP PRIORITY', '2ND PRIORITY'])],
            'fungsi_instansi' => ['nullable', 'string'],
            'website_resmi' => ['nullable', 'url', 'max:255'],
            'struktur_organisasi' => ['nullable', 'string'],
            'alamat_kantor' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:100'],
            'fax' => ['nullable', 'string', 'max:100'],
            'email_perusahaan' => ['nullable', 'email', 'max:255'],
        ];
    }

    private function memberRules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'nama_panggilan' => ['nullable', 'string', 'max:255'],
            'nama_pasangan' => ['nullable', 'string', 'max:255'],
            'nama_anak' => ['nullable', 'string'],
            'nama_keluarga_lainnya' => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'media_sosial' => ['nullable', 'string'],
            'telepon_protokol' => ['nullable', 'string', 'max:100'],
            'telepon_sekretaris' => ['nullable', 'string', 'max:100'],
            'telepon_staff' => ['nullable', 'string', 'max:100'],
            'hobi' => ['nullable', 'string'],
            'makanan_favorit' => ['nullable', 'string'],
            'minuman_favorit' => ['nullable', 'string'],
            'olahraga_favorit' => ['nullable', 'string'],
            'riwayat_pendidikan' => ['nullable', 'string'],
            'sumber_data_url' => ['nullable', 'url', 'max:2048'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    private function ensureMemberBelongsToStakeholder(Stakeholder $stakeholder, StakeholderMember $member): void
    {
        abort_unless($member->stakeholder_id === $stakeholder->id, 404);
    }
}
