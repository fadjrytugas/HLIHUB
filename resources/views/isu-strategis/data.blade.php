<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isSearch ? 'Hasil Pencarian' : 'Tracking Kegiatan/Isu Strategis' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-open-tracking="{{ session('tracking_issue_no', '') }}">
    @include('partials.sidebar')
    <main class="data-page">
        <header class="data-page__header">
            <div class="data-page__header-frame">
                <div class="data-page__header-copy">
                    <span class="section-kicker">{{ $isSearch ? 'Pencarian monitoring' : 'Database monitoring' }}</span>
                    <h1>{{ $isSearch ? 'Hasil Pencarian' : 'Tracking Kegiatan/Isu Strategis' }}</h1>
                    <p>{{ $isSearch ? ($searchTerm !== '' ? 'Menampilkan kegiatan berdasarkan nama, status, unit, atau PIC serta PKS, MoU, peraturan, dan pasal yang cocok dengan kata kunci "' . $searchTerm . '".' : 'Masukkan kata kunci untuk mencari kegiatan berdasarkan status, PKS, MoU, stakeholder, peraturan, atau pasal.') : 'Seluruh data kegiatan dan isu strategis yang tersimpan di database.' }}</p>
                </div>
            @if ($isSearch)
                <a href="{{ url('/') }}" class="hero-action">← Kembali ke dashboard</a>
            @else
                <a href="{{ route('isu-strategis.create') }}" class="hero-action">+ Tambah Isu</a>
            @endif
            </div>
        </header>
        @if ($isSearch)
            @if ($legalProductArchives->isNotEmpty())
            <section class="panel legal-search-results legal-product-search-results" aria-label="Hasil pencarian produk hukum">
                <div class="panel-heading">
                    <div><span class="section-kicker">Arsip Produk Hukum</span><h2>PKS dan MoU yang Ditemukan</h2></div>
                    <span class="legal-count">{{ $legalProductArchives->total() }} hasil</span>
                </div>
                @forelse ($legalProductArchives as $archive)
                    <article class="legal-search-result">
                        <div class="legal-search-result__heading">
                            <span class="legal-document-icon legal-product-search-result__icon" aria-hidden="true">{{ $archive->type === 'mou' ? 'MoU' : 'PKS' }}</span>
                            <div>
                                <span class="section-kicker">{{ $archive->type === 'mou' ? 'MoU' : 'PKS' }} · {{ $archive->stakeholder }}</span>
                                <h3>{{ $archive->nama_dokumen }}</h3>
                                @if ($archive->addenda->isNotEmpty())
                                    <span class="legal-product-addendum-flag">ADA ADENDUM · {{ $archive->addenda->count() }}</span>
                                    <p class="legal-product-search-result__meta"><strong>Perubahan terakhir:</strong> {{ $archive->addenda->last()->perubahan }}</p>
                                @endif
                                <p class="legal-product-search-result__meta">
                                    Periode {{ $archive->tanggal_mulai->format('d M Y') }}–{{ $archive->tanggal_berakhir->format('d M Y') }}
                                    · Unit {{ $archive->unit_pelaksana }}
                                    · File {{ $archive->document_original_name }}
                                </p>
                                <a href="{{ route('legal-products.index', $archive->type) }}#archive-{{ $archive->id }}">Buka arsip {{ $archive->type === 'mou' ? 'MoU' : 'PKS' }} →</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="legal-search-empty">Tidak ada arsip PKS atau MoU yang cocok dengan kata kunci ini.</p>
                @endforelse
                @if ($legalProductArchives->hasPages())
                    <div class="data-pagination" aria-label="Navigasi halaman hasil arsip produk hukum">
                        <span class="data-pagination__summary">Menampilkan {{ $legalProductArchives->firstItem() }}–{{ $legalProductArchives->lastItem() }} dari {{ $legalProductArchives->total() }} arsip</span>
                        <div class="data-pagination__links">
                            @if ($legalProductArchives->onFirstPage())
                                <span class="data-pagination__link data-pagination__link--disabled">Sebelumnya</span>
                            @else
                                <a class="data-pagination__link" href="{{ $legalProductArchives->previousPageUrl() }}">Sebelumnya</a>
                            @endif
                            @for ($page = 1; $page <= $legalProductArchives->lastPage(); $page++)
                                @if ($page === $legalProductArchives->currentPage())
                                    <span class="data-pagination__link data-pagination__link--active">{{ $page }}</span>
                                @else
                                    <a class="data-pagination__link" href="{{ $legalProductArchives->url($page) }}">{{ $page }}</a>
                                @endif
                            @endfor
                            @if ($legalProductArchives->hasMorePages())
                                <a class="data-pagination__link" href="{{ $legalProductArchives->nextPageUrl() }}">Berikutnya</a>
                            @else
                                <span class="data-pagination__link data-pagination__link--disabled">Berikutnya</span>
                            @endif
                        </div>
                    </div>
                @endif
            </section>
            @endif
            @if ($legalRegulations->isNotEmpty())
            <section class="panel legal-search-results" aria-label="Hasil pencarian dasar hukum">
                <div class="panel-heading">
                    <div><span class="section-kicker">Dasar Hukum/Peraturan</span><h2>Peraturan dan Pasal yang Ditemukan</h2></div>
                    <span class="legal-count">{{ $legalRegulations->total() }} hasil</span>
                </div>
                @forelse ($legalRegulations as $regulation)
                    <article class="legal-search-result">
                        <div class="legal-search-result__heading">
                            <span class="legal-document-icon" aria-hidden="true">§</span>
                            <div><span class="section-kicker">{{ config('dasar_hukum.' . $regulation->module_slug, $regulation->module_slug) }}</span><h3>{{ $regulation->nama_peraturan }}</h3></div>
                        </div>
                        @forelse ($regulation->pasal as $article)
                            <div class="legal-search-result__article">
                                <strong>{{ $article->nomor_pasal }}</strong>
                                <p>{{ \Illuminate\Support\Str::limit($article->penjelasan, 240) }}</p>
                                <a href="{{ route('dasar-hukum.show', $regulation->module_slug) }}?open={{ $regulation->id }}&article={{ $article->id }}#regulation-{{ $regulation->id }}">Buka peraturan dan pasal →</a>
                            </div>
                        @empty
                            <div class="legal-search-result__article">
                                <span>Nama peraturan cocok dengan kata kunci.</span>
                                <a href="{{ route('dasar-hukum.show', $regulation->module_slug) }}?open={{ $regulation->id }}#regulation-{{ $regulation->id }}">Buka peraturan →</a>
                            </div>
                        @endforelse
                    </article>
                @empty
                    <p class="legal-search-empty">Tidak ada peraturan atau pasal yang cocok dengan kata kunci ini.</p>
                @endforelse
                @if ($legalRegulations->hasPages())
                    <div class="data-pagination" aria-label="Navigasi halaman hasil peraturan">
                        <span class="data-pagination__summary">Menampilkan {{ $legalRegulations->firstItem() }}–{{ $legalRegulations->lastItem() }} dari {{ $legalRegulations->total() }} peraturan</span>
                        <div class="data-pagination__links">
                            @if ($legalRegulations->onFirstPage())
                                <span class="data-pagination__link data-pagination__link--disabled">Sebelumnya</span>
                            @else
                                <a class="data-pagination__link" href="{{ $legalRegulations->previousPageUrl() }}">Sebelumnya</a>
                            @endif
                            @for ($page = 1; $page <= $legalRegulations->lastPage(); $page++)
                                @if ($page === $legalRegulations->currentPage())
                                    <span class="data-pagination__link data-pagination__link--active">{{ $page }}</span>
                                @else
                                    <a class="data-pagination__link" href="{{ $legalRegulations->url($page) }}">{{ $page }}</a>
                                @endif
                            @endfor
                            @if ($legalRegulations->hasMorePages())
                                <a class="data-pagination__link" href="{{ $legalRegulations->nextPageUrl() }}">Berikutnya</a>
                            @else
                                <span class="data-pagination__link data-pagination__link--disabled">Berikutnya</span>
                            @endif
                        </div>
                    </div>
                @endif
            </section>
            @endif
        @endif
        <section class="panel issues-panel data-panel">
            <div class="panel-heading issues-heading">
                <div><span class="section-kicker">Daftar data</span><h2>{{ $isSearch ? 'Data yang Ditemukan' : ($searchTerm !== '' ? 'Hasil Pencarian' : 'Seluruh Isu Strategis') }}</h2></div>
                @if (! $isSearch)
                    <form class="search-box" action="{{ route('isu-strategis.data') }}" method="GET">
                        <span aria-hidden="true">⌕</span>
                        <input id="issueSearch" name="q" type="search" value="{{ $searchTerm }}" placeholder="Cari isu..." aria-label="Cari isu">
                        <button class="search-submit" type="submit">Cari</button>
                    </form>
                @endif
            </div>
            <div class="table-wrap">
                @php
                    $pointList = static function (?string $text): string {
                        if (blank($text)) {
                            return '-';
                        }

                        $escaped = e(trim($text));
                        $items = preg_split('/(?=\d+\.\s)/', $escaped, -1, PREG_SPLIT_NO_EMPTY);

                        if (count($items) < 2 || !preg_match('/^\d+\.\s/', $items[0])) {
                            return nl2br($escaped);
                        }

                        $listItems = collect($items)->map(function (string $item): string {
                            return '<li>' . nl2br(preg_replace('/^\d+\.\s/', '', trim($item))) . '</li>';
                        })->implode('');

                        return '<ol class="point-list">' . $listItems . '</ol>';
                    };
                @endphp
                <table id="issuesTable">
                    <thead><tr><th>NO</th><th>UNIT/LEMBAGA PENGUSUL</th><th>PIC PENGUSUL</th><th>STAKEHOLDER</th><th>KLASIFIKASI STRATEGIS</th><th>KEGIATAN</th><th>DESKRIPSI KEGIATAN</th><th>RINCIAN KEGIATAN</th><th>GOALS</th><th>PROGRES</th><th>STATUS</th><th>PIC</th><th>PIC HHI/HLI</th><th>SLA</th><th>SLA HARI KERJA</th><th>DURASI AKTUAL</th><th>HAMBATAN/KENDALA</th><th>DAMPAK</th><th>TERAKHIR DI UPDATE</th><th>KETERANGAN UPDATE</th><th>AKSI</th></tr></thead>
                    <tbody>
                        @forelse ($issues as $issue)
                            @php
                                $currentSla = $issue->tracking->firstWhere('status', 'ACTIVE')
                                    ?? $issue->tracking->where('status', 'COMPLETED')->sortByDesc('selesai_pada')->first();
                                $durationExceeded = $currentSla && $issue->durasi_aktual > $currentSla->sla_hari_kerja;
                            @endphp
                            <tr>
                                <td>{{ $issue->no }}</td>
                                <td>{{ $issue->unit_lembaga_pengusul ?: '-' }}</td>
                                <td>{{ $issue->pic_orang_unit_lembaga_pengusul ?: '-' }}</td>
                                <td>{{ $issue->stakeholder ?: '-' }}</td>
                                <td>{{ $issue->klasifikasi_strategis ?: '-' }}</td>
                                <td><strong>{{ $issue->kegiatan }}</strong></td>
                                <td>{!! $pointList($issue->deskripsi_kegiatan) !!}</td>
                                <td>{!! $pointList($issue->rincian_kegiatan) !!}</td>
                                <td>{!! $pointList($issue->goals) !!}</td>
                                <td><div class="progress-cell"><span><i style="width:{{ $issue->progres }}%"></i></span><b>{{ $issue->progres }}%</b></div></td>
                                <td><span class="status-badge {{ in_array(strtoupper($issue->status), ['SELESAI', 'DONE', 'COMPLETED'], true) ? 'status-badge--done' : (strtoupper($issue->status) === 'PENDING' ? 'status-badge--pending' : 'status-badge--progress') }}">{{ $issue->status }}</span></td>
                                <td>{{ $issue->pic ?: '-' }}</td>
                                <td>{{ $issue->pic_orang_pic ?: '-' }}</td>
                                <td>{{ $issue->sla ?: '-' }}</td>
                                <td>{{ $issue->sla_hari_kerja ?? '-' }}</td>
                                <td>
                                    @if ($issue->durasi_aktual !== null)
                                        <span class="duration-indicator {{ $durationExceeded ? 'duration-indicator--late' : 'duration-indicator--within' }}">{{ $issue->durasi_aktual }} hari</span>
                                        @if ($currentSla)<small class="duration-indicator__note">{{ $durationExceeded ? 'Melebihi SLA' : 'Sesuai SLA' }}</small>@endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{!! $pointList($issue->hambatan_kendala) !!}</td>
                                <td>{!! $pointList($issue->dampak) !!}</td>
                                <td>{{ optional($issue->terakhir_di_update)->format('d M Y H:i') ?: '-' }}</td>
                                <td>{{ $issue->keterangan_update ?: '-' }}</td>
                                <td><div class="issue-actions"><button type="button" class="tracking-link tracking-button" data-tracking-open="trackingModal{{ $issue->no }}">Tracking</button><button type="button" class="resume-button" data-resume="{{ e(json_encode(['kegiatan' => $issue->kegiatan, 'unit' => $issue->unit_lembaga_pengusul, 'pic_unit' => $issue->pic_orang_unit_lembaga_pengusul, 'pic' => $issue->pic, 'pic_person' => $issue->pic_orang_pic, 'stakeholder' => $issue->stakeholder, 'klasifikasi' => $issue->klasifikasi_strategis, 'status' => $issue->status, 'progres' => $issue->progres, 'rincian' => $issue->rincian_kegiatan, 'deskripsi' => $issue->deskripsi_kegiatan, 'hambatan' => $issue->hambatan_kendala, 'dampak' => $issue->dampak, 'goals' => $issue->goals, 'updated' => optional($issue->terakhir_di_update)->format('d M Y H:i'), 'update_note' => $issue->keterangan_update], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) }}">Resume</button><a href="{{ route('isu-strategis.edit', $issue) }}" class="edit-button">Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="21" class="empty-state">{{ $isSearch ? ($legalRegulations->isEmpty() && $legalProductArchives->isEmpty() ? 'Tidak ada kegiatan, produk hukum, peraturan, atau pasal yang cocok.' : 'Tidak ada kegiatan yang cocok; periksa hasil produk hukum di atas.') : ($searchTerm !== '' ? 'Tidak ada kegiatan yang cocok dengan kata kunci ini.' : 'Belum ada data isu strategis.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($issues->hasPages())
                <div class="data-pagination" aria-label="Navigasi halaman data">
                    <span class="data-pagination__summary">
                        Menampilkan {{ $issues->firstItem() }}–{{ $issues->lastItem() }} dari {{ $issues->total() }} data
                    </span>
                    <div class="data-pagination__links">
                        @if ($issues->onFirstPage())
                            <span class="data-pagination__link data-pagination__link--disabled">Sebelumnya</span>
                        @else
                            <a class="data-pagination__link" href="{{ $issues->previousPageUrl() }}">Sebelumnya</a>
                        @endif
                        @for ($page = 1; $page <= $issues->lastPage(); $page++)
                            @if ($page === $issues->currentPage())
                                <span class="data-pagination__link data-pagination__link--active">{{ $page }}</span>
                            @else
                                <a class="data-pagination__link" href="{{ $issues->url($page) }}">{{ $page }}</a>
                            @endif
                        @endfor
                        @if ($issues->hasMorePages())
                            <a class="data-pagination__link" href="{{ $issues->nextPageUrl() }}">Berikutnya</a>
                        @else
                            <span class="data-pagination__link data-pagination__link--disabled">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </main>
    @foreach ($issues as $issue)
        @php
            $trackingModalId = 'trackingModal' . $issue->no;
            $trackingActive = $issue->tracking->firstWhere('status', 'ACTIVE');
            $trackingSelected = $trackingActive
                ?? $issue->tracking->where('status', 'COMPLETED')->sortByDesc('selesai_pada')->first();
            $trackingDone = in_array(strtoupper((string) $issue->status), ['SELESAI', 'DONE', 'COMPLETED'], true);
            $slaUnassigned = $issue->isSlaUnassigned();
        @endphp
        <div class="tracking-modal tracking-modal--hidden" id="{{ $trackingModalId }}" role="dialog" aria-modal="true" aria-labelledby="{{ $trackingModalId }}Title" data-tracking-modal>
            <section class="tracking-modal__card">
                <button type="button" class="tracking-modal__close" aria-label="Tutup tracking">&times;</button>
                <header class="tracking-modal__heading">
                    <div><span class="section-kicker">Tracking kegiatan #{{ $issue->no }}</span><h2 id="{{ $trackingModalId }}Title">{{ $issue->kegiatan }}</h2><p>{{ $issue->unit_lembaga_pengusul ?: '-' }} · PIC {{ $issue->pic ?: '-' }} · PIC Pengusul {{ $issue->pic_orang_unit_lembaga_pengusul ?: '-' }} · PIC HHI/HLI {{ $issue->pic_orang_pic ?: '-' }}</p></div>
                    <span class="status-badge {{ $trackingDone ? 'status-badge--done' : 'status-badge--progress' }}">{{ $issue->status }}</span>
                </header>
                @if ((int) session('tracking_issue_no') === $issue->no && session('success'))
                    <div class="success-alert">{{ session('success') }}</div>
                @endif
                @if ((int) session('tracking_issue_no') === $issue->no && session('error'))
                    <div class="form-alert">{{ session('error') }}</div>
                @endif
                @if ($slaUnassigned)
                    <p class="tracking-modal__notice">SLA dan durasi aktual dikosongkan sementara sampai jenis SLA kegiatan ini ditentukan.</p>
                @elseif (!$trackingActive && !$trackingDone)
                    <p class="tracking-modal__notice">Pilih SLA yang diminta pengusul. Hanya SLA terpilih yang akan dikerjakan.</p>
                    <form class="tracking-select-form" method="POST" action="{{ route('tracking.start', $issue) }}">
                        @csrf
                        <label class="form-field"><span>Jenis SLA</span><select name="sla_stage" required><option value="">Pilih SLA</option>@foreach ($slaOptions as $slaOption)<option value="{{ $slaOption->tahap_ke }}">{{ $slaOption->nama_tahap }} · {{ $slaOption->sla_hari_kerja }} hari kerja</option>@endforeach</select></label>
                        <button class="tracking-complete" type="submit">Mulai SLA</button>
                    </form>
                @elseif (!$trackingSelected)
                    <p class="tracking-modal__notice">Kegiatan ini sudah selesai dan tidak memiliki riwayat SLA.</p>
                @else
                    <div class="tracking-modal__summary">
                        SLA {{ $trackingDone ? 'dikerjakan' : 'aktif' }}: <strong>{{ $trackingSelected?->nama_tahap ?: '-' }}</strong>
                        @if ($trackingSelected)
                            <span>{{ $issue->durasi_aktual ?? 0 }} dari {{ $trackingSelected->sla_hari_kerja }} hari kerja SLA</span>
                            <b class="duration-indicator {{ ($issue->durasi_aktual ?? 0) > $trackingSelected->sla_hari_kerja ? 'duration-indicator--late' : 'duration-indicator--within' }}">{{ ($issue->durasi_aktual ?? 0) > $trackingSelected->sla_hari_kerja ? 'Melebihi SLA' : 'Sesuai SLA' }}</b>
                        @endif
                    </div>
                    <div class="tracking-steps tracking-modal__steps">
                        @if ($trackingSelected)
                            @php($stage = $trackingSelected)
                            <div class="tracking-step tracking-step--{{ strtolower($stage->status) }}">
                                <span class="tracking-step__number">{{ $stage->tahap_ke }}</span>
                                <div class="tracking-step__body"><strong>{{ $stage->nama_tahap }}</strong><small>SLA {{ $stage->sla_hari_kerja }} hari kerja · Durasi aktual {{ $issue->durasi_aktual ?? 0 }} hari kerja · @if ($stage->mulai_pada) Mulai {{ $stage->mulai_pada->format('d M Y') }} · @endif Status {{ $stage->status }}</small></div>
                                @if ($stage->status === 'ACTIVE')
                                    <form method="POST" action="{{ route('tracking.complete', $stage) }}">@csrf<button class="tracking-complete" type="submit">Selesaikan Kegiatan</button></form>
                                @elseif ($stage->status === 'COMPLETED')
                                    <span class="tracking-check">✓</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    @endforeach
    <div class="resume-modal resume-modal--hidden" id="resumeModal" role="dialog" aria-modal="true" aria-labelledby="resumeModalTitle">
        <div class="resume-modal__card">
            <button type="button" class="resume-modal__close" aria-label="Tutup resume">&times;</button>
            <h2 id="resumeModalTitle">Resume Kegiatan</h2>
            <div class="resume-hero"><span class="resume-hero__brand">PT KAI · ACTIVITY INTELLIGENCE</span><h3 data-resume-field="kegiatan"></h3><span class="resume-hero__category" data-resume-field="klasifikasi"></span><div class="resume-hero__progress"><strong data-resume-field="progres"></strong><span>PROGRESS</span><b data-resume-field="status"></b></div></div>
            <div class="resume-meta"><div><b>🏢 Unit</b><span data-resume-field="unit"></span></div><div><b>♟ PIC Pengusul</b><span data-resume-field="pic_unit"></span></div><div><b>👤 PIC</b><span data-resume-field="pic"></span></div><div><b>♟ PIC HHI/HLI</b><span data-resume-field="pic_person"></span></div><div><b>👥 Stakeholder</b><span data-resume-field="stakeholder"></span></div></div>
            <div class="resume-grid resume-grid--summary"><section class="resume-box resume-box--status"><h4>✓ Status Kegiatan</h4><strong data-resume-field="status"></strong><span data-resume-field="status"></span></section><section class="resume-box"><h4>◔ Progress Kegiatan</h4><strong data-resume-field="progres"></strong><div class="resume-progress"><i data-resume-field="progress-bar"></i></div></section><section class="resume-box"><h4>◷ Terakhir Update</h4><strong data-resume-field="updated"></strong><p data-resume-field="update_note"></p></section></div>
            <div class="resume-grid resume-grid--details"><section class="resume-box"><h4>📌 Rincian Kegiatan</h4><p data-resume-field="rincian"></p></section><section class="resume-box"><h4>ℹ Informasi Kegiatan</h4><dl><dt>Unit Pengusul/Lembaga</dt><dd data-resume-field="unit"></dd><dt>PIC Pengusul</dt><dd data-resume-field="pic_unit"></dd><dt>Klasifikasi Strategi</dt><dd data-resume-field="klasifikasi"></dd><dt>Stakeholder</dt><dd data-resume-field="stakeholder"></dd><dt>PIC</dt><dd data-resume-field="pic"></dd><dt>PIC HHI/HLI</dt><dd data-resume-field="pic_person"></dd><dt>Status</dt><dd data-resume-field="status"></dd><dt>Progres</dt><dd data-resume-field="progres"></dd></dl></section></div>
            <section class="resume-box"><h4>📝 Deskripsi Kegiatan</h4><p data-resume-field="deskripsi"></p></section>
            <div class="resume-grid"><section class="resume-box resume-box--warning"><h4>⚠ Hambatan / Kendala</h4><p data-resume-field="hambatan"></p></section><section class="resume-box resume-box--impact"><h4>⚡ Dampak / Konsekuensi</h4><p data-resume-field="dampak"></p></section></div>
            <div class="resume-grid"><section class="resume-box"><h4>✅ Hasil Evaluasi & Rekomendasi</h4><p>-</p></section><section class="resume-box"><h4>📝 Tindak Lanjut yang Dilakukan</h4><p data-resume-field="goals"></p></section></div>
            <button type="button" class="resume-modal__footer-close"><span aria-hidden="true">×</span> Tutup Resume</button>
        </div>
    </div>
</body>
</html>
