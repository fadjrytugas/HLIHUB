<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring Isu Strategis</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @if (session('success'))
        <div class="success-modal" id="successModal" role="status" aria-live="polite">
            <div class="success-modal__card">
                <button type="button" class="success-modal__close" aria-label="Tutup notifikasi">&times;</button>
                <div class="success-modal__icon" aria-hidden="true">✓</div>
                <strong>Isu Strategis</strong>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @include('partials.sidebar')
    <main class="dashboard-shell dashboard-shell--with-sidebar">
        <header class="hero">
            <div class="hero-decoration hero-decoration--one"></div>
            <div class="hero-decoration hero-decoration--two"></div>
            <img class="hero-divider" src="{{ asset('images/header-divider.png') }}" alt="" aria-hidden="true">
            <div class="brand-mark"><img src="{{ asset('images/logo-hli-hub-horizontal.png') }}" alt="HLI HUB — Strategic Monitoring &amp; Regulatory Information"></div>
            <div class="hero-tools">
                <div class="notification-menu">
                    <button class="notification-trigger" type="button" id="notificationTrigger" aria-label="Buka notifikasi, {{ $notifications->count() }} notifikasi" aria-expanded="false" aria-controls="notificationDropdown">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                        @if ($notifications->isNotEmpty())
                            <span class="notification-badge" data-notification-badge aria-hidden="true">{{ $notifications->count() > 99 ? '99+' : $notifications->count() }}</span>
                        @endif
                    </button>
                    <div class="notification-dropdown" id="notificationDropdown" aria-labelledby="notificationTitle" hidden>
                        <div class="notification-dropdown__header">
                            <div>
                                <strong id="notificationTitle">Notifikasi</strong>
                                <span>{{ $notifications->count() }} pengingat untuk Anda</span>
                            </div>
                            <button class="notification-dropdown__close" type="button" aria-label="Tutup notifikasi">&times;</button>
                        </div>
                        <div class="notification-list">
                            @forelse ($notifications as $notification)
                                <a class="notification-item notification-item--{{ $notification['type'] }}{{ isset($notification['expiry_status']) ? ' notification-item--legal-product-'.$notification['expiry_status'] : '' }}" href="{{ $notification['url'] }}" data-notification-id="{{ $notification['id'] }}">
                                    <span class="notification-item__icon" aria-hidden="true">
                                        @if ($notification['type'] === 'sla')
                                            SLA
                                        @elseif ($notification['type'] === 'deadline')
                                            {{ $notification['days_remaining'] === 0 ? 'HARI' : 'H-'.$notification['days_remaining'] }}
                                        @elseif ($notification['type'] === 'legal-product')
                                            {{ $notification['document_type'] }}
                                        @else
                                            ULT
                                        @endif
                                    </span>
                                    <span class="notification-item__content">
                                        <span class="notification-item__heading">
                                            <span class="notification-item__title">{{ $notification['title'] }}</span>
                                            <span class="notification-item__read-state" data-notification-read-state></span>
                                        </span>
                                        <strong>{{ $notification['subject'] }}</strong>
                                        <span class="notification-item__description">{{ $notification['description'] }}</span>
                                        <span class="notification-item__date">{{ $notification['date_label'] }}: {{ $notification['date'] }}</span>
                                    </span>
                                    <span class="notification-item__arrow" aria-hidden="true">›</span>
                                    <span class="notification-item__unread-dot" aria-hidden="true"></span>
                                </a>
                            @empty
                                <p class="notification-empty">Tidak ada notifikasi baru. Semua kegiatan dan pengingat Anda aman.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <form class="dashboard-search" method="GET" action="{{ route('isu-strategis.search') }}" role="search">
                    <div class="dashboard-search__control">
                        <span aria-hidden="true">⌕</span>
                        <input id="dashboardSearch" name="q" type="search" value="{{ $searchTerm }}" placeholder="Cari kegiatan, PKS, MoU, stakeholder..." aria-label="Cari kegiatan, status, stakeholder, PKS, MoU, atau peraturan" autocomplete="off">
                        <button type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </header>

        <form class="dashboard-filter" method="GET" action="{{ url('/') }}">
            <label for="picFilter">Tampilkan berdasarkan PIC</label>
            <select id="picFilter" name="pic" aria-label="Pilih PIC">
                <option value="">Keseluruhan</option>
                @foreach ($picOptions as $pic)
                    <option value="{{ $pic }}" @selected($selectedPic === $pic)>{{ $pic }}</option>
                @endforeach
            </select>
            <input type="hidden" name="period" value="{{ $trendPeriod }}">
        </form>

        <section class="kpi-grid" aria-label="Ringkasan indikator utama">
            <article class="kpi-card kpi-card--navy">
                <div class="kpi-icon">⚑</div>
                <div class="kpi-content">
                    <span class="kpi-label">Total Isu Strategis</span>
                    <strong class="kpi-value">{{ $metrics['total'] }}</strong>
                    <span class="kpi-note">Jumlah kegiatan isu strategis yang sedang dipantau.</span>
                </div>
                <span class="kpi-trend">0%</span>
            </article>
            <article class="kpi-card kpi-card--green">
                <div class="kpi-icon">✓</div>
                <div class="kpi-content">
                    <span class="kpi-label">Selesai</span>
                    <strong class="kpi-value">{{ $metrics['done'] }}</strong>
                    <span class="kpi-note">Jumlah kegiatan strategis yang telah selesai.</span>
                </div>
                <span class="kpi-trend">{{ number_format($statusPercentages['done'], 1, ',', '.') }}%</span>
            </article>
            <article class="kpi-card kpi-card--orange">
                <div class="kpi-icon">◷</div>
                <div class="kpi-content">
                    <span class="kpi-label">On Progress</span>
                    <strong class="kpi-value">{{ $metrics['progress'] }}</strong>
                    <span class="kpi-note">Jumlah kegiatan strategis yang sedang berjalan.</span>
                </div>
                <span class="kpi-trend">{{ number_format($statusPercentages['progress'], 1, ',', '.') }}%</span>
            </article>
            <article class="kpi-card kpi-card--blue">
                <div class="kpi-icon">▣</div>
                <div class="kpi-content">
                    <span class="kpi-label">Unit Pengusul / Lembaga</span>
                    <strong class="kpi-value">{{ $metrics['units'] }}</strong>
                    <span class="kpi-note">Jumlah unit pengusul/lembaga unik.</span>
                </div>
                <span class="kpi-trend">0 unit</span>
            </article>
            <article class="kpi-card kpi-card--pink">
                <div class="kpi-icon">♧</div>
                <div class="kpi-content">
                    <span class="kpi-label">Stakeholder Unik</span>
                    <strong class="kpi-value">{{ $metrics['stakeholders'] }}</strong>
                    <span class="kpi-note">Jumlah stakeholder unik yang terlibat.</span>
                </div>
                <span class="kpi-trend">0%</span>
            </article>
        </section>

        <section class="content-grid">
            <article class="panel chart-panel">
                <div class="panel-heading">
                    <div>
                        <span class="section-kicker">Performa monitoring</span>
                        <h2>Tren Isu Strategis</h2>
                    </div>
                    <select id="trendPeriod" class="period-select" aria-label="Pilih periode grafik">
                        <option value="6" @selected($trendPeriod === 6)>6 bulan terakhir</option>
                        <option value="12" @selected($trendPeriod === 12)>12 bulan terakhir</option>
                    </select>
                    <form id="trend-period-form" method="GET" action="{{ url('/') }}" hidden>
                        <input type="hidden" name="period" value="{{ $trendPeriod }}">
                        <input type="hidden" name="pic" value="{{ $selectedPic }}">
                    </form>
                </div>
                <div class="chart-legend">
                    <span><i class="legend-dot legend-dot--navy"></i>Total isu</span>
                    <span><i class="legend-dot legend-dot--orange"></i>Isu selesai</span>
                </div>
                <div class="line-chart" role="img" aria-label="Grafik tren isu strategis dari Mei hingga Oktober">
                    <div class="chart-y-axis"><span>60</span><span>45</span><span>30</span><span>15</span><span>0</span></div>
                    <div class="chart-area">
                        <div class="grid-line"></div><div class="grid-line"></div><div class="grid-line"></div><div class="grid-line"></div><div class="grid-line"></div>
                        <svg viewBox="0 0 600 220" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="areaFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#34428d" stop-opacity=".22"/><stop offset="1" stop-color="#34428d" stop-opacity="0"/></linearGradient>
                            </defs>
                            <polyline class="chart-area-fill" points="0,220 {{ $trend['totalPoints'] }} 600,220"/>
                            <polyline class="chart-line chart-line--navy" points="{{ $trend['totalPoints'] }}"/>
                            <polyline class="chart-line chart-line--orange" points="{{ $trend['donePoints'] }}"/>
                        </svg>
                        <div class="chart-x-axis">@foreach ($trend['labels'] as $label)<span>{{ $label }}</span>@endforeach</div>
                    </div>
                </div>
            </article>

            <article class="panel status-panel">
                <div class="panel-heading">
                    <div><span class="section-kicker">Status terkini</span><h2>Distribusi Status</h2></div>
                    <button class="icon-button" aria-label="Lihat detail status">•••</button>
                </div>
                <div class="donut-wrap">
                    <div class="donut-chart" style="background: conic-gradient(#209358 0 {{ $statusPercentages['done'] }}%, #ed8a27 {{ $statusPercentages['done'] }}% {{ $statusPercentages['done'] + $statusPercentages['progress'] }}%, #d7dce6 {{ $statusPercentages['done'] + $statusPercentages['progress'] }}% 100%)"><div><strong>{{ $metrics['total'] }}</strong><span>Total isu</span></div></div>
                    <div class="status-list">
                        <div><span class="status-name"><i class="status-dot status-dot--green"></i>Selesai</span><strong>{{ $metrics['done'] }} <small>{{ number_format($statusPercentages['done'], 1, ',', '.') }}%</small></strong></div>
                        <div><span class="status-name"><i class="status-dot status-dot--orange"></i>On Progress</span><strong>{{ $metrics['progress'] }} <small>{{ number_format($statusPercentages['progress'], 1, ',', '.') }}%</small></strong></div>
                        <div><span class="status-name"><i class="status-dot status-dot--gray"></i>Belum dimulai</span><strong>{{ $metrics['pending'] }} <small>{{ number_format($statusPercentages['pending'], 1, ',', '.') }}%</small></strong></div>
                    </div>
                </div>
                <div class="completion-bar"><span style="width:{{ $statusPercentages['done'] }}%"></span></div>
                <p class="completion-copy"><strong>{{ number_format($statusPercentages['done'], 1, ',', '.') }}%</strong> isu strategis telah dituntaskan</p>
            </article>
        </section>

        <section class="analysis-section">
            <div class="analysis-grid">
                <article class="panel analysis-card">
                    <div class="panel-heading"><div><span class="section-kicker">Kontributor kegiatan</span><h2>Jumlah Unit Pengusul/Lembaga dengan Kegiatan Terbanyak</h2></div><button class="icon-button" aria-label="Opsi grafik">•••</button></div>
                    <div class="bar-chart">
                        @foreach ($unitBreakdown as $unit)
                            <div class="bar-row"><span>{{ $unit['label'] }}</span><i><b style="width:{{ ($unit['count'] / max(1, $unitBreakdown->max('count'))) * 100 }}%"></b></i><em>{{ $unit['count'] }}</em></div>
                        @endforeach
                    </div>
                    <div class="axis-label">Jumlah Kegiatan</div>
                </article>
                <article class="panel analysis-card">
                    <div class="panel-heading"><div><span class="section-kicker">Klasifikasi program</span><h2>Distribusi Klasifikasi Strategi</h2></div><strong class="total-label">Total {{ $metrics['total'] }}</strong></div>
                    <div class="classification-chart">
                        @php
                            $classificationColors = ['#302d78', '#ed7200', '#ef9755', '#615db1', '#4a478f', '#208b51', '#176ba6'];
                        @endphp
                        @foreach ($classificationBreakdown as $index => $classification)
                            <div class="classification-row"><span>{{ $classification['label'] }}</span><i><b style="width:{{ ($classification['count'] / max(1, $classificationBreakdown->max('count'))) * 100 }}%;background:{{ $classificationColors[$index % count($classificationColors)] }}"></b></i><em>{{ $classification['count'] }}</em></div>
                        @endforeach
                    </div>
                    <div class="classification-key"><span><i style="background:#302d78"></i>Klasifikasi Strategis</span><span>Total: {{ $classificationBreakdown->sum('count') }}</span></div>
                </article>
                <article class="panel analysis-card">
                    <div class="panel-heading"><div><span class="section-kicker">Stakeholder utama</span><h2>Top 25 Stakeholder berdasarkan Keterlibatan</h2></div><button class="icon-button" aria-label="Opsi grafik">•••</button></div>
                    <div class="lollipop-chart">
                        @foreach ($stakeholderBreakdown as $stakeholder)
                            <div class="lollipop-row"><span>{{ $stakeholder['label'] }}</span><i><b style="width:{{ ($stakeholder['count'] / max(1, $stakeholderBreakdown->max('count'))) * 100 }}%"></b></i><em>{{ $stakeholder['count'] }}</em></div>
                        @endforeach
                    </div>
                    <div class="axis-label">Jumlah Keterlibatan</div>
                </article>
            </div>
        </section>

        <section class="panel issues-panel priority-panel">
            <div class="panel-heading issues-heading">
                <div><span class="section-kicker">Daftar prioritas</span><h2>Kegiatan On Progress</h2></div>
                <span class="priority-flag"><span aria-hidden="true">!</span> PRIORITAS</span>
            </div>
            <div class="table-wrap">
                <table id="issuesTable">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>UNIT/LEMBAGA PENGUSUL</th>
                            <th>PIC PENGUSUL</th>
                            <th>STAKEHOLDER</th>
                            <th>KLASIFIKASI STRATEGIS</th>
                            <th>KEGIATAN</th>
                            <th>TANGGAL MASUK</th>
                            <th>DEADLINE</th>
                            <th>DESKRIPSI KEGIATAN</th>
                            <th>GOALS</th>
                            <th>PROGRES</th>
                            <th>STATUS</th>
                            <th>PIC</th>
                            <th>PIC HHI/HLI</th>
                            <th>SLA</th>
                            <th>SLA HARI KERJA</th>
                            <th>DURASI AKTUAL</th>
                            <th>HAMBATAN/KENDALA</th>
                            <th>DAMPAK</th>
                            <th>TERAKHIR DI UPDATE</th>
                            <th>KETERANGAN UPDATE</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
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
                        @forelse ($priorityIssues->take(4) as $issue)
                            <tr>
                                <td>{{ $issue->no }}</td>
                                <td>{{ $issue->unit_lembaga_pengusul ?: '-' }}</td>
                                <td>{{ $issue->pic_orang_unit_lembaga_pengusul ?: '-' }}</td>
                                <td>{{ $issue->stakeholder ?: '-' }}</td>
                                <td>{{ $issue->klasifikasi_strategis ?: '-' }}</td>
                                <td><strong>{{ $issue->kegiatan }}</strong></td>
                                <td>{{ optional($issue->tanggal_masuk)->format('d M Y') ?: '-' }}</td>
                                <td>{{ optional($issue->deadline)->format('d M Y') ?: '-' }}</td>
                                <td>{!! $pointList($issue->deskripsi_kegiatan) !!}</td>
                                <td>{!! $pointList($issue->goals) !!}</td>
                                <td><div class="progress-cell"><span><i style="width:{{ $issue->progres }}%"></i></span><b>{{ $issue->progres }}%</b></div></td>
                                @php
                                    $statusClass = match (strtoupper($issue->status)) {
                                        'SELESAI', 'DONE', 'COMPLETED' => 'status-badge--done',
                                        'PENDING' => 'status-badge--pending',
                                        default => 'status-badge--progress',
                                    };
                                @endphp
                                <td><span class="status-badge {{ $statusClass }}">{{ $issue->status }}</span></td>
                                <td>{{ $issue->pic ?: '-' }}</td>
                                <td>{{ $issue->pic_orang_pic ?: '-' }}</td>
                                <td>{{ $issue->sla ?: '-' }}</td>
                                <td>{{ $issue->sla_hari_kerja ?? '-' }}</td>
                                <td>{{ $issue->durasi_aktual ?? '-' }}</td>
                                <td>{!! $pointList($issue->hambatan_kendala) !!}</td>
                                <td>{!! $pointList($issue->dampak) !!}</td>
                                <td>{{ optional($issue->terakhir_di_update)->format('d M Y H:i') ?: '-' }}</td>
                                <td>{{ $issue->keterangan_update ?: '-' }}</td>
                                <td><div class="issue-actions"><button type="button" class="resume-button" data-resume="{{ e(json_encode(['kegiatan' => $issue->kegiatan, 'unit' => $issue->unit_lembaga_pengusul, 'pic_unit' => $issue->pic_orang_unit_lembaga_pengusul, 'pic' => $issue->pic, 'pic_person' => $issue->pic_orang_pic, 'stakeholder' => $issue->stakeholder, 'klasifikasi' => $issue->klasifikasi_strategis, 'status' => $issue->status, 'progres' => $issue->progres, 'rincian' => $issue->rincian_kegiatan, 'deskripsi' => $issue->deskripsi_kegiatan, 'hambatan' => $issue->hambatan_kendala, 'dampak' => $issue->dampak, 'goals' => $issue->goals, 'updated' => optional($issue->terakhir_di_update)->format('d M Y H:i'), 'update_note' => $issue->keterangan_update], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) }}">Resume</button><a href="{{ route('isu-strategis.edit', $issue) }}" class="edit-button">Edit</a><form method="POST" action="{{ route('isu-strategis.destroy', $issue) }}" data-confirm-delete data-delete-name="{{ $issue->kegiatan }}">@csrf @method('DELETE')<button type="submit" class="issue-delete-button">Hapus</button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="22" class="empty-state">Belum ada kegiatan yang sedang berjalan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-footer"><span>Menampilkan <strong>{{ min(4, $priorityIssues->count()) }}</strong> dari {{ $priorityIssues->count() }} kegiatan berjalan</span><a href="{{ route('isu-strategis.data') }}" class="view-all">Lihat semua isu <span>→</span></a></div>
        </section>

        <footer class="dashboard-footer">
            <span class="dashboard-footer__brand">HLI HUB <span>Strategic Monitoring &amp; Regulatory Information</span></span>
            <span>© {{ date('Y') }} PT Kereta Api Indonesia (Persero)</span>
        </footer>
    </main>
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
    @include('partials.delete-confirm-modal')
</body>
</html>
