<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $stakeholder->nama }} · Stakeholder</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')
    <main class="data-page stakeholder-page stakeholder-detail-page">
        <a class="stakeholder-back-link legal-primary-button" href="{{ route('stakeholders.index') }}">
            <span aria-hidden="true">←</span> Kembali ke daftar stakeholder
        </a>
        <header class="stakeholder-profile-header">
            <div class="stakeholder-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M3 20h18M5 20V7l7-4 7 4v13M9 20v-6h6v6M8 9h.01M12 9h.01M16 9h.01"/></svg>
            </div>
            <div class="stakeholder-profile-header__copy">
                <span class="stakeholder-priority stakeholder-priority--{{ $stakeholder->prioritas === 'TOP PRIORITY' ? 'top' : 'second' }}">{{ $stakeholder->prioritas }}</span>
                <h1>{{ $stakeholder->nama }}</h1>
                <p>{{ $stakeholder->fungsi_instansi ?: 'Fungsi instansi belum diisi.' }}</p>
            </div>
            <details class="stakeholder-edit">
                <summary class="legal-secondary-button">Edit Informasi</summary>
                <form class="stakeholder-form stakeholder-edit__form" method="POST" action="{{ route('stakeholders.update', $stakeholder) }}">
                    @csrf
                    @method('PUT')
                    <h2>Edit Informasi Stakeholder</h2>
                    <div class="stakeholder-form-grid">
                        <label class="legal-field">Nama Stakeholder / Instansi <b>*</b>
                            <input type="text" name="nama" value="{{ $stakeholder->nama }}" maxlength="255" required>
                        </label>
                        <label class="legal-field">Tingkat Prioritas <b>*</b>
                            <select name="prioritas" required>
                                <option value="TOP PRIORITY" @selected($stakeholder->prioritas === 'TOP PRIORITY')>TOP Priority</option>
                                <option value="2ND PRIORITY" @selected($stakeholder->prioritas === '2ND PRIORITY')>2ND Priority</option>
                            </select>
                        </label>
                        <label class="legal-field">Fungsi Instansi
                            <textarea name="fungsi_instansi" rows="3">{{ $stakeholder->fungsi_instansi }}</textarea>
                        </label>
                        <label class="legal-field">Website Resmi
                            <input type="url" name="website_resmi" value="{{ $stakeholder->website_resmi }}" placeholder="https://">
                        </label>
                        <label class="legal-field">Struktur Organisasi
                            <textarea name="struktur_organisasi" rows="3">{{ $stakeholder->struktur_organisasi }}</textarea>
                        </label>
                        <label class="legal-field">Alamat Kantor
                            <textarea name="alamat_kantor" rows="3">{{ $stakeholder->alamat_kantor }}</textarea>
                        </label>
                        <label class="legal-field">Telepon
                            <input type="text" name="telepon" value="{{ $stakeholder->telepon }}" maxlength="100">
                        </label>
                        <label class="legal-field">Fax
                            <input type="text" name="fax" value="{{ $stakeholder->fax }}" maxlength="100">
                        </label>
                        <label class="legal-field">Email Perusahaan
                            <input type="email" name="email_perusahaan" value="{{ $stakeholder->email_perusahaan }}" maxlength="255">
                        </label>
                    </div>
                    <div class="stakeholder-form__actions"><button class="legal-primary-button" type="submit">Simpan Perubahan</button></div>
                </form>
            </details>
            <form method="POST" action="{{ route('stakeholders.destroy', $stakeholder) }}" data-confirm="Hapus stakeholder {{ $stakeholder->nama }} beserta seluruh anggotanya?">
                @csrf
                @method('DELETE')
                <button class="legal-danger-button" type="submit">Hapus Stakeholder</button>
            </form>
        </header>

        @if (session('success'))
            <div class="legal-alert legal-alert--success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="legal-alert legal-alert--error" role="alert">
                <strong>Periksa kembali data yang dimasukkan.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="panel stakeholder-info-panel">
            <div class="panel-heading"><div><span class="section-kicker">Profil institusi</span><h2>Informasi Stakeholder</h2></div></div>
            <dl class="stakeholder-info-grid">
                <div><dt>Fungsi Instansi</dt><dd>{{ $stakeholder->fungsi_instansi ?: '-' }}</dd></div>
                <div><dt>Website Resmi</dt><dd>@if ($stakeholder->website_resmi)<a href="{{ $stakeholder->website_resmi }}" target="_blank" rel="noopener noreferrer">{{ $stakeholder->website_resmi }}</a>@else - @endif</dd></div>
                <div><dt>Struktur Organisasi</dt><dd>{{ $stakeholder->struktur_organisasi ?: '-' }}</dd></div>
                <div><dt>Alamat Kantor</dt><dd>{{ $stakeholder->alamat_kantor ?: '-' }}</dd></div>
                <div><dt>Telepon</dt><dd>{{ $stakeholder->telepon ?: '-' }}</dd></div>
                <div><dt>Fax</dt><dd>{{ $stakeholder->fax ?: '-' }}</dd></div>
                <div><dt>Email Perusahaan</dt><dd>{{ $stakeholder->email_perusahaan ?: '-' }}</dd></div>
            </dl>
        </section>

        <section class="stakeholder-members-section">
            <div class="stakeholder-members-heading">
                <div><span class="section-kicker">Kontak dan relasi</span><h2>Anggota Stakeholder <span>{{ $stakeholder->members->count() }}</span></h2></div>
                <details class="stakeholder-add-member" @if ($errors->any() && old('nama_lengkap')) open @endif>
                    <summary class="legal-primary-button"><span aria-hidden="true">+</span> Tambah Anggota</summary>
                    <button class="stakeholder-member__backdrop" type="button" aria-label="Tutup formulir"></button>
                    <form class="stakeholder-form stakeholder-member-form" method="POST" action="{{ route('stakeholders.members.store', $stakeholder) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="stakeholder-member-form__heading">
                            <h3>Informasi Anggota Baru</h3>
                            <button class="stakeholder-member__close" type="button" aria-label="Tutup formulir">&times;</button>
                        </div>
                        @include('stakeholder.partials.member-fields', ['member' => null, 'formId' => 'new-member-' . $stakeholder->id])
                        <div class="stakeholder-form__actions"><button class="legal-primary-button" type="submit">Simpan Anggota</button></div>
                    </form>
                </details>
            </div>

            <div class="stakeholder-members-grid">
                @forelse ($stakeholder->members as $member)
                    <article class="panel stakeholder-member-card">
                        <div class="stakeholder-member-card__top">
                            <div class="stakeholder-member-avatar">
                                @if ($member->foto_path)
                                    <img src="{{ route('stakeholders.members.photo', [$stakeholder, $member]) }}" alt="Foto {{ $member->nama_lengkap }}">
                                @else
                                    <span aria-hidden="true">{{ mb_strtoupper(mb_substr($member->nama_lengkap, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="stakeholder-member-card__identity">
                                <h3>{{ $member->nama_lengkap }}</h3>
                                <p>{{ $member->jabatan ?: 'Jabatan belum diisi' }}</p>
                                @if ($member->nama_panggilan)<span>Panggilan: {{ $member->nama_panggilan }}</span>@endif
                            </div>
                            <span class="stakeholder-member-card__chevron" aria-hidden="true">⌄</span>
                        </div>
                        <details class="stakeholder-member-details">
                            <summary>Lihat profil lengkap</summary>
                            <dl class="stakeholder-member-profile">
                                <div><dt>Nama Panggilan</dt><dd>{{ $member->nama_panggilan ?: '-' }}</dd></div>
                                <div><dt>Jenis Kelamin</dt><dd>{{ $member->jenis_kelamin ?: '-' }}</dd></div>
                                <div><dt>Tempat, Tanggal Lahir</dt><dd>{{ trim(($member->tempat_lahir ?: '-') . ', ' . ($member->tanggal_lahir?->format('d M Y') ?: '-'), ', ') }}</dd></div>
                                <div><dt>Nama Istri/Suami</dt><dd>{{ $member->nama_pasangan ?: '-' }}</dd></div>
                                <div><dt>Nama Anak</dt><dd>{{ $member->nama_anak ?: '-' }}</dd></div>
                                <div><dt>Keluarga Lainnya</dt><dd>{{ $member->nama_keluarga_lainnya ?: '-' }}</dd></div>
                                <div><dt>Media Sosial</dt><dd>{{ $member->media_sosial ?: '-' }}</dd></div>
                                <div><dt>No. Tlp. Protokol</dt><dd>{{ $member->telepon_protokol ?: '-' }}</dd></div>
                                <div><dt>No. Tlp. Sekretaris</dt><dd>{{ $member->telepon_sekretaris ?: '-' }}</dd></div>
                                <div><dt>No. Tlp. Staff</dt><dd>{{ $member->telepon_staff ?: '-' }}</dd></div>
                                <div><dt>Hobi</dt><dd>{{ $member->hobi ?: '-' }}</dd></div>
                                <div><dt>Makanan Favorit</dt><dd>{{ $member->makanan_favorit ?: '-' }}</dd></div>
                                <div><dt>Minuman Favorit</dt><dd>{{ $member->minuman_favorit ?: '-' }}</dd></div>
                                <div><dt>Olahraga Favorit</dt><dd>{{ $member->olahraga_favorit ?: '-' }}</dd></div>
                                <div class="stakeholder-member-profile__wide"><dt>Riwayat Pendidikan</dt><dd>{{ $member->riwayat_pendidikan ?: '-' }}</dd></div>
                                <div class="stakeholder-member-profile__wide"><dt>Sumber Data</dt><dd>
                                    @if ($member->sumber_data_url)
                                        <a href="{{ $member->sumber_data_url }}" target="_blank" rel="noopener noreferrer">{{ $member->sumber_data_url }}</a>
                                    @else
                                        Belum dicantumkan
                                    @endif
                                </dd></div>
                            </dl>
                            <div class="stakeholder-member-actions">
                                <details class="stakeholder-member-edit">
                                    <summary class="legal-secondary-button">Edit Anggota</summary>
                                    <form class="stakeholder-form stakeholder-member-form" method="POST" action="{{ route('stakeholders.members.update', [$stakeholder, $member]) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <h3>Edit {{ $member->nama_lengkap }}</h3>
                                        @include('stakeholder.partials.member-fields', ['member' => $member, 'formId' => 'edit-member-' . $member->id])
                                        <div class="stakeholder-form__actions"><button class="legal-primary-button" type="submit">Simpan Perubahan</button></div>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('stakeholders.members.destroy', [$stakeholder, $member]) }}" data-confirm="Hapus anggota {{ $member->nama_lengkap }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="legal-danger-button" type="submit">Hapus Anggota</button>
                                </form>
                            </div>
                        </details>
                    </article>
                @empty
                    <div class="panel stakeholder-empty stakeholder-empty--directory">
                        <strong>Belum ada anggota</strong>
                        <span>Tambahkan anggota untuk mulai melengkapi profil stakeholder.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
