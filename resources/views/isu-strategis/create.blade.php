<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $issue ? 'Edit Isu Strategis' : 'Tambah Isu Strategis' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="form-shell">
        <div class="form-topbar"><a href="{{ url('/') }}" class="back-link">← Kembali ke dashboard</a><span class="category-pill">Kategori: STRATEGIS</span></div>
        <section class="form-card">
            <div class="form-heading"><span class="section-kicker">{{ $issue ? 'Perbarui data monitoring' : 'Input data monitoring' }}</span><h1>{{ $issue ? 'Edit Isu Strategis #' . $issue->no : 'Tambah Isu Strategis' }}</h1><p>{{ $issue ? 'Perbarui data kegiatan. SLA aktif dan durasi aktual dikelola melalui tracking.' : 'Pilih jenis SLA sesuai permintaan pengusul. Kegiatan langsung dimulai pada SLA yang dipilih.' }}</p></div>
            @if ($errors->any())
                <div class="form-alert"><strong>Periksa kembali isian:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ $issue ? route('isu-strategis.update', $issue) : route('isu-strategis.store') }}">
                @csrf
                @if ($issue)
                    @method('PUT')
                @endif
                <div class="form-grid">
                    @php($fields = [
                        ['unit_lembaga_pengusul', 'Unit/Lembaga Pengusul', 'text', true],
                        ['pic_orang_unit_lembaga_pengusul', 'PIC Pengusul', 'text', false],
                        ['stakeholder', 'Stakeholder', 'text', true],
                        ['klasifikasi_strategis', 'Klasifikasi Strategis', 'text', true],
                        ['kegiatan', 'Kegiatan', 'textarea', true],
                        ['deskripsi_kegiatan', 'Deskripsi Kegiatan', 'textarea', false],
                        ['rincian_kegiatan', 'Rincian Kegiatan', 'textarea', false],
                        ['goals', 'Goals', 'textarea', false],
                        ['pic', 'PIC', 'text', false],
                        ['pic_orang_pic', 'PIC HHI/HLI', 'text', false],
                        ['hambatan_kendala', 'Hambatan/Kendala', 'textarea', false],
                        ['dampak', 'Dampak', 'textarea', false],
                        ['tanggal_masuk', 'Tanggal Masuk Kegiatan', 'date', true],
                        ['deadline', 'Deadline Kegiatan', 'date', true],
                        ['terakhir_di_update', 'Terakhir Di Update', 'datetime-local', false],
                        ['keterangan_update', 'Keterangan Update', 'textarea', false],
                    ])
                    @foreach ($fields as [$name, $label, $type, $required])
                        <label class="form-field {{ $type === 'textarea' ? 'form-field--wide' : '' }}">{{ $label }} @if ($required)<b>*</b>@endif
                            @if ($type === 'textarea')
                                <textarea name="{{ $name }}" rows="3" {{ $required ? 'required' : '' }}>{{ old($name, $issue?->{$name}) }}</textarea>
                                @if ($name === 'keterangan_update')
                                    <small>Jelaskan perubahan terakhir, misalnya progres, tindak lanjut, atau kendala yang diperbarui.</small>
                                @endif
                            @else
                                <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, in_array($type, ['date', 'datetime-local'], true) ? optional($issue?->{$name})->format($type === 'datetime-local' ? 'Y-m-d\TH:i' : 'Y-m-d') : $issue?->{$name}) }}" {{ $required ? 'required' : '' }} min="{{ in_array($name, ['sla_hari_kerja', 'durasi_aktual'], true) ? 0 : '' }}">
                            @endif
                        </label>
                    @endforeach
                    <label class="form-field"><span>Progres (%) <b>*</b></span><input type="number" name="progres" value="{{ old('progres', $issue?->progres ?? 0) }}" min="0" max="100" step="1" required><small>Isi progres kegiatan secara manual dari 0 sampai 100%.</small></label>
                    @if ($issue)
                        <label class="form-field"><span>Status</span><input type="text" value="{{ $issue->status }}" readonly><small>Status menjadi SELESAI saat SLA yang sedang dikerjakan diselesaikan; progres tetap mengikuti isian manual.</small></label>
                        <label class="form-field"><span>SLA Aktif</span><input type="text" value="{{ $issue->sla ?: '-' }}" readonly></label>
                        <label class="form-field"><span>SLA Hari Kerja</span><input type="number" value="{{ $issue->sla_hari_kerja ?? '' }}" readonly></label>
                        <label class="form-field"><span>Durasi Aktual (hari kerja)</span><input type="number" value="{{ $issue->durasi_aktual ?? 0 }}" readonly><small>Dihitung otomatis dari awal pengerjaan SLA.</small></label>
                    @else
                        <label class="form-field"><span>Jenis SLA yang diminta <b>*</b></span><select name="sla_stage" required><option value="">Pilih SLA</option>@foreach ($slaOptions as $slaOption)<option value="{{ $slaOption->tahap_ke }}" @selected((string) old('sla_stage') === (string) $slaOption->tahap_ke)>{{ $slaOption->nama_tahap }} ({{ $slaOption->sla_hari_kerja }} hari kerja)</option>@endforeach</select>@if ($slaOptions->isEmpty())<small>Belum ada jenis SLA. Tambahkan terlebih dahulu melalui Pengaturan &gt; Pengaturan SLA.</small>@endif</label>
                        <label class="form-field"><span>Status</span><input type="text" value="ON PROGRESS (dimulai otomatis)" readonly><small>Status menjadi SELESAI saat SLA yang dipilih diselesaikan; progres diisi manual.</small></label>
                        <label class="form-field"><span>Durasi Aktual (hari kerja)</span><input type="text" value="Dihitung otomatis" readonly><small>Durasi aktual dibandingkan dengan batas SLA.</small></label>
                    @endif
                </div>
                <div class="form-actions"><a href="{{ url('/') }}" class="cancel-button">Batal</a><button type="submit" class="save-button">{{ $issue ? 'Perbarui Isu Strategis' : 'Simpan Isu Strategis' }}</button></div>
            </form>
        </section>
    </main>
</body>
</html>
