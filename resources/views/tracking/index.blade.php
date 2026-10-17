<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan SLA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')
    <main class="data-page">
        <header class="data-page__header">
            <div>
                <span class="section-kicker">Workflow monitoring</span>
                <h1>Pengaturan SLA</h1>
                <p>Atur jenis permintaan dan durasi SLA. Saat kegiatan dibuat, pilih langsung jenis SLA yang diminta pengusul.</p>
            </div>
        </header>

        @if (session('success'))<div class="success-alert" role="status">{{ session('success') }}</div>@endif
        @if ($errors->any())
            <div class="form-alert" role="alert">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="panel tracking-config">
            <div class="tracking-config__intro">
                <div><span class="section-kicker">Konfigurasi SLA</span><h2>Daftar Jenis SLA</h2></div>
                <span>{{ $stages->count() }} jenis</span>
            </div>
            <p class="tracking-config__note">Setiap jenis SLA dipilih langsung sesuai permintaan pengusul, tidak dijalankan sebagai tahapan berurutan. Perubahan batas SLA juga berlaku untuk kegiatan yang masih aktif. Jenis SLA yang masih dipakai kegiatan aktif tidak dapat dihapus.</p>
            <div class="tracking-config__list">
                @forelse ($stages as $stage)
                    <article class="tracking-config__row">
                        <span class="tracking-config__number" aria-label="Jenis SLA">{{ $loop->iteration }}</span>
                        <form class="tracking-config__edit" method="POST" action="{{ route('tracking.stages.update', $stage) }}">
                            @csrf
                            @method('PUT')
                            <label class="tracking-config__field tracking-config__field--name">
                                <span>Nama SLA</span>
                                <input type="text" name="nama_tahap" value="{{ $stage->nama_tahap }}" maxlength="255" required>
                            </label>
                            <label class="tracking-config__field tracking-config__field--sla">
                                <span>Durasi SLA (hari kerja)</span>
                                <input type="number" name="sla_hari_kerja" value="{{ $stage->sla_hari_kerja }}" min="0" required>
                            </label>
                            <button class="tracking-config__save" type="submit">Simpan</button>
                        </form>
                        <form class="tracking-config__delete" method="POST" action="{{ route('tracking.stages.destroy', $stage) }}" data-confirm="Hapus jenis SLA ini? Riwayat penggunaan sebelumnya akan tetap disimpan.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" aria-label="Hapus SLA {{ $stage->nama_tahap }}">Hapus</button>
                        </form>
                    </article>
                @empty
                    <p class="empty-state">Belum ada jenis SLA.</p>
                @endforelse
            </div>
        </section>

        <section class="panel tracking-config__add">
            <div class="tracking-config__intro">
                <div><span class="section-kicker">Tambah jenis pekerjaan</span><h2>Tambah Jenis SLA</h2></div>
            </div>
            <form class="tracking-config__edit" method="POST" action="{{ route('tracking.stages.store') }}">
                @csrf
                <label class="tracking-config__field tracking-config__field--name">
                    <span>Nama SLA</span>
                    <input type="text" name="nama_tahap" value="{{ old('nama_tahap') }}" placeholder="Contoh: Reviu dan Harmonisasi MoU" maxlength="255" required>
                </label>
                <label class="tracking-config__field tracking-config__field--sla">
                    <span>Durasi SLA (hari kerja)</span>
                    <input type="number" name="sla_hari_kerja" value="{{ old('sla_hari_kerja', 0) }}" min="0" required>
                </label>
                <button class="tracking-config__save" type="submit">Tambah Jenis SLA</button>
            </form>
        </section>
    </main>
</body>
</html>
