<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stakeholder</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')
    <main class="data-page stakeholder-page">
        <header class="data-page__header">
            <div>
                <span class="section-kicker">Direktori Relasi</span>
                <h1>Stakeholder</h1>
                <p>Kelola organisasi stakeholder, tingkat prioritas, informasi institusi, dan anggota terkait.</p>
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

        <section class="stakeholder-toolbar">
            <div><strong>{{ $stakeholderTotal }}</strong><span>organisasi stakeholder</span></div>
            <details class="stakeholder-create" @if ($errors->any()) open @endif>
                <summary class="legal-primary-button"><span aria-hidden="true">+</span> Tambah Stakeholder</summary>
                <button class="stakeholder-create__backdrop" type="button" aria-label="Tutup formulir"></button>
                <form class="stakeholder-form stakeholder-create__form" method="POST" action="{{ route('stakeholders.store') }}">
                    @csrf
                    <div class="stakeholder-create__heading">
                        <h2>Informasi Stakeholder</h2>
                        <button class="stakeholder-create__close" type="button" aria-label="Tutup formulir">&times;</button>
                    </div>
                    <div class="stakeholder-form-grid">
                        <label class="legal-field">Nama Stakeholder / Instansi <b>*</b>
                            <input type="text" name="nama" value="{{ old('nama') }}" maxlength="255" required>
                        </label>
                        <label class="legal-field">Tingkat Prioritas <b>*</b>
                            <select name="prioritas" required>
                                <option value="TOP PRIORITY" @selected(old('prioritas') === 'TOP PRIORITY')>TOP Priority</option>
                                <option value="2ND PRIORITY" @selected(old('prioritas', '2ND PRIORITY') === '2ND PRIORITY')>2ND Priority</option>
                            </select>
                        </label>
                        <label class="legal-field">Fungsi Instansi
                            <textarea name="fungsi_instansi" rows="3">{{ old('fungsi_instansi') }}</textarea>
                        </label>
                        <label class="legal-field">Website Resmi
                            <input type="url" name="website_resmi" value="{{ old('website_resmi') }}" placeholder="https://">
                        </label>
                        <label class="legal-field">Struktur Organisasi
                            <textarea name="struktur_organisasi" rows="3">{{ old('struktur_organisasi') }}</textarea>
                        </label>
                        <label class="legal-field">Alamat Kantor
                            <textarea name="alamat_kantor" rows="3">{{ old('alamat_kantor') }}</textarea>
                        </label>
                        <label class="legal-field">Telepon
                            <input type="text" name="telepon" value="{{ old('telepon') }}" maxlength="100">
                        </label>
                        <label class="legal-field">Fax
                            <input type="text" name="fax" value="{{ old('fax') }}" maxlength="100">
                        </label>
                        <label class="legal-field">Email Perusahaan
                            <input type="email" name="email_perusahaan" value="{{ old('email_perusahaan') }}" maxlength="255">
                        </label>
                    </div>
                    <div class="stakeholder-form__actions">
                        <button class="legal-primary-button" type="submit">Simpan Stakeholder</button>
                    </div>
                </form>
            </details>
        </section>

        <nav class="stakeholder-priority-tabs" aria-label="Filter prioritas stakeholder">
            <a href="{{ route('stakeholders.index', ['priority' => 'TOP PRIORITY']) }}" class="stakeholder-priority-tab {{ $activePriority === 'TOP PRIORITY' ? 'stakeholder-priority-tab--active stakeholder-priority-tab--top' : '' }}" @if ($activePriority === 'TOP PRIORITY') aria-current="page" @endif>
                <span>TOP PRIORITY</span><span class="stakeholder-priority-tab__count">{{ $topPriorityStakeholders->total() }}</span>
            </a>
            <a href="{{ route('stakeholders.index', ['priority' => '2ND PRIORITY']) }}" class="stakeholder-priority-tab {{ $activePriority === '2ND PRIORITY' ? 'stakeholder-priority-tab--active stakeholder-priority-tab--second' : '' }}" @if ($activePriority === '2ND PRIORITY') aria-current="page" @endif>
                <span>2ND PRIORITY</span><span class="stakeholder-priority-tab__count">{{ $secondPriorityStakeholders->total() }}</span>
            </a>
        </nav>

        @foreach ([
            ['title' => 'TOP PRIORITY', 'class' => 'top', 'priority' => 'TOP PRIORITY', 'stakeholders' => $topPriorityStakeholders],
            ['title' => '2ND PRIORITY', 'class' => 'second', 'priority' => '2ND PRIORITY', 'stakeholders' => $secondPriorityStakeholders],
        ] as $priorityGroup)
            @continue($priorityGroup['priority'] !== $activePriority)
            @php($priorityStakeholders = $priorityGroup['stakeholders'])
            <section class="stakeholder-priority-section stakeholder-priority-section--{{ $priorityGroup['class'] }}" aria-labelledby="stakeholder-{{ $priorityGroup['class'] }}-title">
                <header class="stakeholder-priority-section__heading">
                    <div>
                        <h2 id="stakeholder-{{ $priorityGroup['class'] }}-title">{{ $priorityGroup['title'] }}</h2>
                        <span>{{ $priorityStakeholders->total() }} organisasi</span>
                    </div>
                    <span class="stakeholder-priority stakeholder-priority--{{ $priorityGroup['class'] }}">{{ $priorityGroup['title'] }}</span>
                </header>
                <div class="stakeholder-directory" aria-label="Daftar {{ $priorityGroup['title'] }}">
                    @forelse ($priorityStakeholders as $stakeholder)
                        <a class="stakeholder-card" href="{{ route('stakeholders.show', $stakeholder) }}">
                            <div class="stakeholder-card__top">
                                <span class="stakeholder-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M3 20h18M5 20V7l7-4 7 4v13M9 20v-6h6v6M8 9h.01M12 9h.01M16 9h.01"/></svg>
                                </span>
                                <span class="stakeholder-priority stakeholder-priority--{{ $priorityGroup['class'] }}">{{ $stakeholder->prioritas }}</span>
                            </div>
                            <h2>{{ $stakeholder->nama }}</h2>
                            <p>{{ $stakeholder->fungsi_instansi ?: 'Informasi fungsi instansi belum diisi.' }}</p>
                            <div class="stakeholder-card__footer">
                                <span>{{ $stakeholder->members_count }} anggota</span>
                                <span class="stakeholder-card__open">Buka detail <b aria-hidden="true">→</b></span>
                            </div>
                        </a>
                    @empty
                        <div class="panel stakeholder-empty stakeholder-empty--directory">
                            <span class="stakeholder-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M3 20h18M5 20V7l7-4 7 4v13M9 20v-6h6v6M8 9h.01M12 9h.01M16 9h.01"/></svg>
                            </span>
                            <strong>Belum ada {{ $priorityGroup['title'] }}</strong>
                            <span>Stakeholder dengan tingkat prioritas ini akan tampil di sini.</span>
                        </div>
                    @endforelse
                </div>

                @if ($priorityStakeholders->hasPages())
                    <div class="data-pagination" aria-label="Navigasi {{ $priorityGroup['title'] }}">
                        <span class="data-pagination__summary">Menampilkan {{ $priorityStakeholders->firstItem() }}–{{ $priorityStakeholders->lastItem() }} dari {{ $priorityStakeholders->total() }} stakeholder</span>
                        <div class="data-pagination__links">
                            @if ($priorityStakeholders->onFirstPage())
                                <span class="data-pagination__link data-pagination__link--disabled">Sebelumnya</span>
                            @else
                                <a class="data-pagination__link" href="{{ $priorityStakeholders->previousPageUrl() }}">Sebelumnya</a>
                            @endif
                            @for ($page = 1; $page <= $priorityStakeholders->lastPage(); $page++)
                                @if ($page === $priorityStakeholders->currentPage())
                                    <span class="data-pagination__link data-pagination__link--active">{{ $page }}</span>
                                @else
                                    <a class="data-pagination__link" href="{{ $priorityStakeholders->url($page) }}">{{ $page }}</a>
                                @endif
                            @endfor
                            @if ($priorityStakeholders->hasMorePages())
                                <a class="data-pagination__link" href="{{ $priorityStakeholders->nextPageUrl() }}">Berikutnya</a>
                            @else
                                <span class="data-pagination__link data-pagination__link--disabled">Berikutnya</span>
                            @endif
                        </div>
                    </div>
                @endif
            </section>
        @endforeach
    </main>
</body>
</html>
