<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} · Dasar Hukum/Peraturan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')
    <main class="data-page legal-page">
        <header class="data-page__header">
            <div>
                <span class="section-kicker">Dasar Hukum/Peraturan</span>
                <h1>{{ $title }}</h1>
                <p>Kelola peraturan, dokumen, dan pasal yang menjadi dasar hukum {{ $title }}.</p>
            </div>
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

        <section class="panel legal-form-panel">
            <div class="legal-section-heading">
                <div><span class="section-kicker">Daftar regulasi</span><h2>Tambah Peraturan</h2></div>
                <span class="legal-count">{{ $regulations->count() }} peraturan</span>
            </div>
            <form class="legal-regulation-form" method="POST" action="{{ route('dasar-hukum.regulations.store', $slug) }}" enctype="multipart/form-data">
                @csrf
                <label class="legal-field legal-field--wide">Nama Peraturan
                    <input type="text" name="nama_peraturan" value="{{ old('nama_peraturan') }}" placeholder="Contoh: Undang-Undang Nomor 23 Tahun 2007 tentang Perkeretaapian" maxlength="255" required>
                </label>
                <label class="legal-field">Dokumen Peraturan
                    <input type="file" name="document" accept=".pdf,.doc,.docx" required>
                    <small>Format PDF, DOC, atau DOCX; maksimal 20 MB.</small>
                </label>
                <button class="legal-primary-button" type="submit">Tambah Peraturan</button>
            </form>
        </section>

        <section class="legal-regulations" aria-label="Peraturan pada {{ $title }}">
            @forelse ($regulations as $regulation)
                <details class="panel legal-regulation-card" id="regulation-{{ $regulation->id }}" @if ((string) request()->query('open') === (string) $regulation->id) open @endif>
                    <summary class="legal-regulation-summary">
                        <div class="legal-regulation-title">
                            <span class="legal-document-icon" aria-hidden="true">§</span>
                            <div><span class="section-kicker">Peraturan</span><h2>{{ $regulation->nama_peraturan }}</h2></div>
                        </div>
                        <span class="legal-regulation-summary-meta">{{ $regulation->pasal->count() }} pasal <i aria-hidden="true"></i></span>
                    </summary>
                    <div class="legal-regulation-content">
                    <div class="legal-regulation-heading">
                        <div class="legal-regulation-actions">
                            @if (strtolower(pathinfo($regulation->document_original_name, PATHINFO_EXTENSION)) === 'pdf')
                                <button
                                    class="legal-secondary-button"
                                    type="button"
                                    data-document-preview
                                    data-preview-url="{{ route('dasar-hukum.regulations.preview', [$slug, $regulation]) }}"
                                    data-document-title="{{ $regulation->nama_peraturan }}"
                                    data-document-name="{{ $regulation->document_original_name }}"
                                    data-download-url="{{ route('dasar-hukum.regulations.download', [$slug, $regulation]) }}"
                                >Lihat Dokumen</button>
                            @endif
                            <a class="legal-secondary-button" href="{{ route('dasar-hukum.regulations.download', [$slug, $regulation]) }}">Unduh Dokumen</a>
                            <form method="POST" action="{{ route('dasar-hukum.regulations.destroy', [$slug, $regulation]) }}" onsubmit="return confirm('Hapus peraturan ini beserta seluruh pasalnya?')">
                                @csrf
                                @method('DELETE')
                                <button class="legal-danger-button" type="submit">Hapus Peraturan</button>
                            </form>
                        </div>
                    </div>
                    <div class="legal-document-name">Dokumen: {{ $regulation->document_original_name }}</div>

                    <details class="legal-edit-details">
                        <summary>Edit nama atau ganti dokumen</summary>
                        <form class="legal-edit-form" method="POST" action="{{ route('dasar-hukum.regulations.update', [$slug, $regulation]) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <label class="legal-field">Nama Peraturan
                                <input type="text" name="nama_peraturan" value="{{ $regulation->nama_peraturan }}" maxlength="255" required>
                            </label>
                            <label class="legal-field">Ganti Dokumen <span>(opsional)</span>
                                <input type="file" name="document" accept=".pdf,.doc,.docx">
                            </label>
                            <button class="legal-secondary-button" type="submit">Simpan Perubahan</button>
                        </form>
                    </details>

                    <div class="legal-articles-heading"><h3>Pasal</h3><span>{{ $regulation->pasal->count() }} pasal</span></div>
                    @forelse ($regulation->pasal as $article)
                        <details class="legal-article" id="article-{{ $article->id }}" @if ((string) request()->query('article') === (string) $article->id) open @endif>
                            <summary>
                                <h4>{{ $article->nomor_pasal }}</h4>
                                <span>Lihat isi pasal <i aria-hidden="true"></i></span>
                            </summary>
                            <div class="legal-article-content">
                                <p>{{ $article->penjelasan }}</p>
                                <form method="POST" action="{{ route('dasar-hukum.articles.destroy', [$slug, $regulation, $article]) }}" onsubmit="return confirm('Hapus {{ $article->nomor_pasal }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="legal-text-button" type="submit">Hapus Pasal</button>
                                </form>
                            </div>
                        </details>
                    @empty
                        <p class="legal-empty-articles">Belum ada pasal untuk peraturan ini.</p>
                    @endforelse

                    <details class="legal-add-article">
                        <summary><span aria-hidden="true">+</span> Tambah Pasal</summary>
                        <form class="legal-article-form" method="POST" action="{{ route('dasar-hukum.articles.store', [$slug, $regulation]) }}">
                            @csrf
                            <label class="legal-field">Nomor / Judul Pasal
                                <input type="text" name="nomor_pasal" value="{{ old('nomor_pasal') }}" placeholder="Contoh: Pasal 151" maxlength="100" required>
                            </label>
                            <label class="legal-field">Penjelasan Pasal
                                <textarea name="penjelasan" rows="5" placeholder="Masukkan isi atau penjelasan pasal..." required>{{ old('penjelasan') }}</textarea>
                            </label>
                            <button class="legal-primary-button" type="submit">Simpan Pasal</button>
                        </form>
                    </details>
                    </div>
                </details>
            @empty
                <div class="panel legal-empty-state">
                    <div class="legal-module-panel__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M5 3.5h9l5 5v12H5z"/><path d="M14 3.5v5h5M8.5 13h7M8.5 16.5h7"/></svg>
                    </div>
                    <h2>Belum ada peraturan</h2>
                    <p>Tambahkan peraturan pertama untuk kategori {{ $title }} menggunakan formulir di atas.</p>
                </div>
            @endforelse
        </section>
    </main>
    <div class="document-preview-modal document-preview-modal--hidden" id="documentPreviewModal" role="dialog" aria-modal="true" aria-labelledby="documentPreviewTitle">
        <section class="document-preview-modal__card">
            <header class="document-preview-modal__header">
                <div><h2 id="documentPreviewTitle">Dokumen Dasar Hukum</h2><p data-document-title></p><span data-document-name></span></div>
                <button type="button" class="document-preview-modal__close" aria-label="Tutup pratinjau">&times;</button>
            </header>
            <div class="document-preview-modal__viewer">
                <iframe title="Pratinjau dokumen peraturan" data-document-frame></iframe>
            </div>
            <footer class="document-preview-modal__footer">
                <a class="legal-secondary-button" data-document-download href="#">Unduh Dokumen</a>
            </footer>
        </section>
    </div>
</body>
</html>
