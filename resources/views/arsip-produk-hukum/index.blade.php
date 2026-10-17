<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip {{ $title }} · HLI HUB</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')
    <main class="data-page legal-page">
        <header class="data-page__header">
            <div>
                <span class="section-kicker">Arsip Produk Hukum</span>
                <h1>{{ $title }}</h1>
                <p>Kelola ruang lingkup, penandatangan, korespondensi kedua pihak, masa berlaku, dan riwayat adendum {{ $title }}.</p>
            </div>
            <a class="legal-secondary-button" href="{{ route('legal-products.index', $type === 'pks' ? 'mou' : 'pks') }}">Buka {{ $type === 'pks' ? 'MoU' : 'PKS' }}</a>
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

        <details class="panel legal-form-panel legal-product-create" @if ($errors->any()) open @endif>
            <summary class="legal-product-create__summary">
                <span class="legal-product-create__intro">
                    <span class="section-kicker">Kelola dokumen</span>
                    <strong>Tambah Arsip {{ $title }}</strong>
                    <span>Lengkapi ruang lingkup, penandatangan, korespondensi kedua pihak, periode, dan dokumen.</span>
                </span>
                <span class="legal-product-create__actions">
                    <span class="legal-count">{{ $archives->total() }} dokumen</span>
                    <span class="legal-product-create__toggle"><i aria-hidden="true">+</i><span>Tambah arsip</span></span>
                </span>
            </summary>
            <form class="legal-regulation-form legal-product-form" method="POST" action="{{ route('legal-products.store', $type) }}" enctype="multipart/form-data">
                @csrf
                <label class="legal-field legal-field--wide">Nama {{ $title }}
                    <input type="text" name="nama_dokumen" value="{{ old('nama_dokumen') }}" placeholder="Contoh: PKS Kerja Sama Pengembangan SDM" maxlength="255" required>
                </label>
                <label class="legal-field">Nama Stakeholder
                    <input type="text" name="stakeholder" value="{{ old('stakeholder') }}" list="legalProductStakeholders" maxlength="255" placeholder="Pilih atau masukkan nama stakeholder" required>
                </label>
                <label class="legal-field">Unit yang Menjalani
                    <input type="text" name="unit_pelaksana" value="{{ old('unit_pelaksana') }}" maxlength="255" placeholder="Nama unit pelaksana" required>
                </label>
                <label class="legal-field">Tanggal Mulai
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                </label>
                <label class="legal-field">Tanggal Berakhir
                    <input type="date" name="tanggal_berakhir" value="{{ old('tanggal_berakhir') }}" required>
                </label>
                <label class="legal-field">Jangka Waktu
                    <input type="text" name="jangka_waktu" value="{{ old('jangka_waktu') }}" maxlength="100" placeholder="Contoh: 2 tahun" required>
                </label>
                <label class="legal-field legal-field--wide">Ruang Lingkup {{ $title }}
                    <textarea name="ruang_lingkup" rows="3" maxlength="10000" required>{{ old('ruang_lingkup') }}</textarea>
                </label>
                <section class="legal-party-group" aria-label="Informasi para pihak">
                    <h3>Penandatangan dan Korespondensi</h3>
                    <div class="legal-party-group__grid">
                        <label class="legal-field">Nama Penandatangan Pihak Pertama
                            <input type="text" name="penandatangan_pihak_pertama" value="{{ old('penandatangan_pihak_pertama') }}" maxlength="255" required>
                        </label>
                        <label class="legal-field">Nama Penandatangan Pihak Kedua
                            <input type="text" name="penandatangan_pihak_kedua" value="{{ old('penandatangan_pihak_kedua') }}" maxlength="255" required>
                        </label>
                        <label class="legal-field">Korespondensi Pihak Pertama
                            <textarea name="korespondensi_pihak_pertama" rows="5" maxlength="5000" placeholder="Nama/jabatan, alamat surat, telepon, dan surel" required>{{ old('korespondensi_pihak_pertama') }}</textarea>
                        </label>
                        <label class="legal-field">Korespondensi Pihak Kedua
                            <textarea name="korespondensi_pihak_kedua" rows="5" maxlength="5000" placeholder="Nama/jabatan, alamat surat, telepon, dan surel" required>{{ old('korespondensi_pihak_kedua') }}</textarea>
                        </label>
                    </div>
                </section>
                <label class="legal-field legal-field--wide">Dokumen {{ $title }}
                    <input type="file" name="document" accept=".pdf,.doc,.docx" required>
                    <small>Format PDF, DOC, atau DOCX; maksimal 20 MB.</small>
                </label>
                <button class="legal-primary-button" type="submit">Simpan Arsip {{ $title }}</button>
            </form>
            <datalist id="legalProductStakeholders">
                @foreach ($stakeholders as $stakeholder)
                    <option value="{{ $stakeholder }}">
                @endforeach
            </datalist>
        </details>

        <form class="legal-product-toolbar" method="GET" action="{{ route('legal-products.index', $type) }}" role="search">
            <label class="legal-product-search">
                <span aria-hidden="true">⌕</span>
                <input type="search" name="q" value="{{ $searchTerm }}" placeholder="Cari nama dokumen, stakeholder, unit, atau adendum..." aria-label="Cari arsip {{ $title }}">
            </label>
            <button class="legal-secondary-button" type="submit">Cari arsip</button>
            @if ($searchTerm !== '')
                <a class="legal-product-clear-search" href="{{ route('legal-products.index', $type) }}">Hapus pencarian</a>
            @endif
            <span class="legal-product-result-count">
                @if ($archives->total() > 0)
                    Menampilkan {{ $archives->firstItem() }}–{{ $archives->lastItem() }} dari {{ $archives->total() }} dokumen
                @else
                    0 dokumen ditemukan
                @endif
            </span>
        </form>

        <section class="legal-regulations legal-product-archives" aria-label="Daftar arsip {{ $title }}">
            @forelse ($archives as $archive)
                @php
                    $isExpired = $archive->isExpired();
                    $isExpiringSoon = $archive->isExpiringWithinThreeMonths();
                    $daysRemaining = now()->startOfDay()->diffInDays($archive->tanggal_berakhir, false);
                @endphp
                <article class="panel legal-product-card {{ $isExpired ? 'legal-product-card--expired' : ($isExpiringSoon ? 'legal-product-card--expiring' : '') }}" id="archive-{{ $archive->id }}">
                    <div class="legal-product-card__heading">
                        <div>
                            <span class="section-kicker">{{ $title }}</span>
                            <h2>{{ $archive->nama_dokumen }}</h2>
                        </div>
                        @if ($isExpired)
                            <span class="legal-product-expiry-flag">SUDAH BERAKHIR</span>
                        @elseif ($isExpiringSoon)
                            <span class="legal-product-expiry-flag">AKAN BERAKHIR · H-3 BULAN</span>
                        @endif
                        @if ($archive->addenda->isNotEmpty())
                            <span class="legal-product-addendum-flag">ADA ADENDUM · {{ $archive->addenda->count() }}</span>
                        @endif
                    </div>
                    <dl class="legal-product-card__summary">
                        <div><dt>Stakeholder</dt><dd>{{ $archive->stakeholder }}</dd></div>
                        <div><dt>Unit pelaksana</dt><dd>{{ $archive->unit_pelaksana }}</dd></div>
                        <div><dt>Jangka waktu</dt><dd>{{ $archive->jangka_waktu }}</dd></div>
                        <div><dt>Berakhir</dt><dd>{{ $archive->tanggal_berakhir->format('d M Y') }}</dd></div>
                        @if ($isExpired || $isExpiringSoon)
                            <div class="legal-product-card__status">
                                <dt>Status</dt>
                                <dd>
                                    @if ($isExpired)
                                        Berakhir {{ abs($daysRemaining) }} hari yang lalu
                                    @elseif ($daysRemaining === 0)
                                        Berakhir hari ini
                                    @else
                                        Berakhir dalam {{ $daysRemaining }} hari
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                    <div class="legal-product-card__actions">
                        <a class="legal-secondary-button" href="{{ route('legal-products.download', [$type, $archive]) }}">Unduh Dokumen</a>
                        <button class="legal-secondary-button" type="button" data-open-archive-edit="archive-edit-{{ $archive->id }}">Edit Arsip</button>
                        <form method="POST" action="{{ route('legal-products.destroy', [$type, $archive]) }}" onsubmit="return confirm('Hapus arsip {{ $title }} ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="legal-danger-button" type="submit">Hapus Arsip</button>
                        </form>
                    </div>
                    <details class="legal-product-detail" @if ($errors->any() && old('archive_id') == $archive->id) open @endif>
                        <summary><span>Lihat detail lengkap</span><span aria-hidden="true">⌄</span></summary>
                    <dl class="legal-product-meta">
                        <div><dt>Nama file</dt><dd>{{ $archive->document_original_name }}</dd></div>
                        <div><dt>Stakeholder</dt><dd>{{ $archive->stakeholder }}</dd></div>
                        <div><dt>Unit yang menjalani</dt><dd>{{ $archive->unit_pelaksana }}</dd></div>
                        <div><dt>Tanggal mulai</dt><dd>{{ $archive->tanggal_mulai->format('d M Y') }}</dd></div>
                        <div><dt>Tanggal berakhir</dt><dd>{{ $archive->tanggal_berakhir->format('d M Y') }}</dd></div>
                        <div><dt>Jangka waktu</dt><dd>{{ $archive->jangka_waktu }}</dd></div>
                        @if ($isExpired || $isExpiringSoon)
                            <div class="legal-product-meta__expiry"><dt>Status masa berlaku</dt><dd>
                                @if ($daysRemaining > 0)
                                    Berakhir dalam {{ $daysRemaining }} hari
                                @elseif ($daysRemaining === 0)
                                    Berakhir hari ini
                                @else
                                    Berakhir {{ abs($daysRemaining) }} hari yang lalu
                                @endif
                            </dd></div>
                        @endif
                    </dl>
                    <section class="legal-product-parties" aria-label="Ruang lingkup, penandatangan, dan korespondensi">
                        <h3>Ruang Lingkup</h3>
                        <p>{{ $archive->ruang_lingkup ?: 'Belum dicantumkan.' }}</p>
                        <div class="legal-product-parties__grid">
                            <div><h4>Penandatangan</h4><p><strong>Pihak Pertama:</strong> {{ $archive->penandatangan_pihak_pertama ?: 'Belum dicantumkan.' }}</p><p><strong>Pihak Kedua:</strong> {{ $archive->penandatangan_pihak_kedua ?: 'Belum dicantumkan.' }}</p></div>
                            <div><h4>Korespondensi Pihak Pertama</h4><p>{{ $archive->korespondensi_pihak_pertama ?: 'Belum dicantumkan.' }}</p></div>
                            <div><h4>Korespondensi Pihak Kedua</h4><p>{{ $archive->korespondensi_pihak_kedua ?: 'Belum dicantumkan.' }}</p></div>
                        </div>
                    </section>
                    <section class="legal-product-addenda" aria-label="Riwayat adendum {{ $archive->nama_dokumen }}">
                        <div class="legal-product-addenda__heading">
                            <h3>Riwayat Adendum</h3>
                            <span>{{ $archive->addenda->count() }} perubahan</span>
                        </div>
                        @forelse ($archive->addenda as $addendum)
                            <article class="legal-product-addendum">
                                <time datetime="{{ $addendum->tanggal_adendum->format('Y-m-d') }}">{{ $addendum->tanggal_adendum->format('d M Y') }}</time>
                                <p>{{ $addendum->perubahan }}</p>
                            </article>
                        @empty
                            <p class="legal-product-addenda__empty">Belum ada adendum yang dicatat.</p>
                        @endforelse
                        <details class="legal-product-addendum-create" @if ($errors->has('tanggal_adendum') || $errors->has('perubahan')) open @endif>
                            <summary>+ Catat adendum atau perubahan</summary>
                            <form class="legal-edit-form legal-product-form" method="POST" action="{{ route('legal-products.addenda.store', [$type, $archive]) }}">
                                @csrf
                                <input type="hidden" name="archive_id" value="{{ $archive->id }}">
                                <label class="legal-field">Tanggal Adendum
                                    <input type="date" name="tanggal_adendum" value="{{ old('tanggal_adendum') }}" required>
                                </label>
                                <label class="legal-field legal-field--wide">Perubahan yang Dilakukan
                                    <textarea name="perubahan" rows="4" maxlength="10000" placeholder="Jelaskan perubahan isi, pasal, masa berlaku, atau ketentuan lainnya" required>{{ old('perubahan') }}</textarea>
                                </label>
                                <button class="legal-secondary-button" type="submit">Simpan Adendum</button>
                            </form>
                        </details>
                    </section>
                    </details>
                    <dialog class="legal-product-edit-modal" id="archive-edit-{{ $archive->id }}" aria-labelledby="archive-edit-title-{{ $archive->id }}" @if ($errors->any() && old('archive_id') == $archive->id) data-reopen @endif>
                        <div class="legal-product-edit-modal__header">
                            <div>
                                <span class="section-kicker">Kelola dokumen</span>
                                <h3 id="archive-edit-title-{{ $archive->id }}">Edit Arsip {{ $title }}</h3>
                                <p>Perbarui informasi, pihak, masa berlaku, atau dokumen.</p>
                            </div>
                            <button class="legal-product-edit-modal__close" type="button" data-close-archive-edit aria-label="Tutup formulir edit">&times;</button>
                        </div>
                        <form class="legal-edit-form legal-product-form" method="POST" action="{{ route('legal-products.update', [$type, $archive]) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="archive_id" value="{{ $archive->id }}">
                            <label class="legal-field legal-field--wide">Nama {{ $title }}
                                <input type="text" name="nama_dokumen" value="{{ old('nama_dokumen', $archive->nama_dokumen) }}" maxlength="255" required>
                            </label>
                            <label class="legal-field">Nama Stakeholder
                                <input type="text" name="stakeholder" value="{{ old('stakeholder', $archive->stakeholder) }}" list="legalProductStakeholders" maxlength="255" required>
                            </label>
                            <label class="legal-field">Unit yang Menjalani
                                <input type="text" name="unit_pelaksana" value="{{ old('unit_pelaksana', $archive->unit_pelaksana) }}" maxlength="255" required>
                            </label>
                            <label class="legal-field">Tanggal Mulai
                                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $archive->tanggal_mulai->format('Y-m-d')) }}" required>
                            </label>
                            <label class="legal-field">Tanggal Berakhir
                                <input type="date" name="tanggal_berakhir" value="{{ old('tanggal_berakhir', $archive->tanggal_berakhir->format('Y-m-d')) }}" required>
                            </label>
                            <label class="legal-field">Jangka Waktu
                                <input type="text" name="jangka_waktu" value="{{ old('jangka_waktu', $archive->jangka_waktu) }}" maxlength="100" placeholder="Contoh: 2 tahun" required>
                            </label>
                            <label class="legal-field legal-field--wide">Ruang Lingkup {{ $title }}
                                <textarea name="ruang_lingkup" rows="3" maxlength="10000" required>{{ old('ruang_lingkup', $archive->ruang_lingkup) }}</textarea>
                            </label>
                            <section class="legal-party-group" aria-label="Informasi para pihak">
                                <h3>Penandatangan dan Korespondensi</h3>
                                <div class="legal-party-group__grid">
                                    <label class="legal-field">Nama Penandatangan Pihak Pertama
                                        <input type="text" name="penandatangan_pihak_pertama" value="{{ old('penandatangan_pihak_pertama', $archive->penandatangan_pihak_pertama) }}" maxlength="255" required>
                                    </label>
                                    <label class="legal-field">Nama Penandatangan Pihak Kedua
                                        <input type="text" name="penandatangan_pihak_kedua" value="{{ old('penandatangan_pihak_kedua', $archive->penandatangan_pihak_kedua) }}" maxlength="255" required>
                                    </label>
                                    <label class="legal-field">Korespondensi Pihak Pertama
                                        <textarea name="korespondensi_pihak_pertama" rows="5" maxlength="5000" required>{{ old('korespondensi_pihak_pertama', $archive->korespondensi_pihak_pertama) }}</textarea>
                                    </label>
                                    <label class="legal-field">Korespondensi Pihak Kedua
                                        <textarea name="korespondensi_pihak_kedua" rows="5" maxlength="5000" required>{{ old('korespondensi_pihak_kedua', $archive->korespondensi_pihak_kedua) }}</textarea>
                                    </label>
                                </div>
                            </section>
                            <label class="legal-field legal-field--wide">Ganti Dokumen <span>(opsional)</span>
                                <input type="file" name="document" accept=".pdf,.doc,.docx">
                                <small>Format PDF, DOC, atau DOCX; maksimal 20 MB.</small>
                            </label>
                            <button class="legal-secondary-button" type="submit">Simpan Perubahan</button>
                        </form>
                    </dialog>
                </article>
            @empty
                <div class="panel legal-empty-state">
                    <div class="legal-module-panel__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M5 3.5h9l5 5v12H5z"/><path d="M14 3.5v5h5M8.5 13h7M8.5 16.5h7"/></svg>
                    </div>
                    <h2>{{ $searchTerm === '' ? 'Belum ada arsip '.$title : 'Arsip tidak ditemukan' }}</h2>
                    <p>{{ $searchTerm === '' ? 'Tambahkan dokumen '.$title.' pertama menggunakan formulir di atas.' : 'Coba kata kunci lain atau hapus pencarian untuk melihat semua arsip.' }}</p>
                    @if ($searchTerm !== '')
                        <a class="legal-secondary-button" href="{{ route('legal-products.index', $type) }}">Tampilkan semua arsip</a>
                    @endif
                </div>
            @endforelse
        </section>
        @if ($archives->hasPages())
            <nav class="data-pagination legal-product-pagination" aria-label="Navigasi halaman arsip {{ $title }}">
                <span class="data-pagination__summary">Halaman {{ $archives->currentPage() }} dari {{ $archives->lastPage() }}</span>
                <div class="data-pagination__links">
                    @if ($archives->onFirstPage())
                        <span class="data-pagination__link data-pagination__link--disabled">Sebelumnya</span>
                    @else
                        <a class="data-pagination__link" href="{{ $archives->previousPageUrl() }}">Sebelumnya</a>
                    @endif
                    @if ($archives->hasMorePages())
                        <a class="data-pagination__link" href="{{ $archives->nextPageUrl() }}">Berikutnya</a>
                    @else
                        <span class="data-pagination__link data-pagination__link--disabled">Berikutnya</span>
                    @endif
                </div>
            </nav>
        @endif
    </main>
</body>
</html>
