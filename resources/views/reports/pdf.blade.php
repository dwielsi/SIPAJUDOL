<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->report_number }}</title>
    <style>
        @page { margin: 100px 60px 70px 60px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #1f2937; line-height: 1.6; }
        header { position: fixed; top: -85px; left: 0; right: 0; height: 78px; border-bottom: 3px double #1e293b; padding-bottom: 6px; }
        header table { width: 100%; }
        header .instansi { font-size: 13px; font-weight: bold; text-transform: uppercase; color: #111827; }
        header .unit { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #111827; }
        header .address { font-size: 9px; color: #4b5563; }
        footer { position: fixed; bottom: -50px; left: 0; right: 0; height: 40px; border-top: 1px solid #d1d5db; padding-top: 6px; font-size: 9px; color: #9ca3af; text-align: center; }
        table.letter-meta { width: 100%; margin-bottom: 10px; }
        table.letter-meta td { padding: 1px 0; vertical-align: top; font-size: 11px; }
        table.letter-meta td.label { width: 80px; }
        table.letter-meta td.colon { width: 10px; }
        .recipient { margin: 14px 0 16px; }
        .recipient .to-label { margin-bottom: 2px; }
        .recipient .to-name { font-weight: bold; }
        p { margin: 0 0 10px; text-align: justify; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #1e293b; margin: 14px 0 5px; }
        table.data { width: 100%; border-collapse: collapse; margin: 4px 0 10px; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; font-size: 10px; }
        table.data th { background: #f3f4f6; text-transform: uppercase; font-size: 9px; color: #4b5563; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-slate { background: #f1f5f9; color: #475569; }
        .body-text { white-space: pre-line; }
        .closing { margin-top: 12px; }
        .signature-table { width: 100%; margin-top: 28px; }
        .signature-table td { vertical-align: top; font-size: 11px; }
        .signature-block { width: 260px; text-align: center; }
        .signature-block .place-date { text-align: left; margin-bottom: 4px; }
        .signature-block .role { margin-bottom: 55px; }
        .signature-block .name { font-weight: bold; text-decoration: underline; }
        .signature-block .nip { margin-top: 2px; }
        .tembusan { margin-top: 30px; font-size: 10px; }
        .tembusan .title { text-decoration: underline; margin-bottom: 3px; }
    </style>
</head>
<body>
    <header>
        <table>
            <tr>
                <td style="width: 100%; text-align: center;">
                    <div class="instansi">Pemerintah Kabupaten Kubu Raya</div>
                    <div class="unit">{{ $setting->instansi_name ?? config('app.name', 'SIPAJUDOL') }}</div>
                    <div class="address">{{ $setting->address ?? '-' }}</div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Dokumen ini dicetak melalui {{ config('app.name', 'SIPAJUDOL') }} pada {{ now()->translatedFormat('d M Y, H:i') }} WIB
    </footer>

    <table class="letter-meta">
        <tr>
            <td class="label">Nomor</td>
            <td class="colon">:</td>
            <td>{{ $report->report_number }}</td>
            @php
                $city = 'Sungai Raya';
                if ($setting?->address && count($addressParts = explode(',', $setting->address)) > 1) {
                    $city = trim($addressParts[1]);
                }
            @endphp
            <td style="width: 40%; text-align: right;">{{ $city }}, {{ $report->report_date->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Sifat</td>
            <td class="colon">:</td>
            <td>Penting</td>
            <td></td>
        </tr>
        <tr>
            <td class="label">Lampiran</td>
            <td class="colon">:</td>
            <td>{{ $report->scanResult && $report->scanResult->findings->isNotEmpty() ? '1 (satu) berkas' : '-' }}</td>
            <td></td>
        </tr>
        <tr>
            <td class="label">Perihal</td>
            <td class="colon">:</td>
            <td><strong>Pemberitahuan Hasil Pemeriksaan Indikasi Konten Judi Online{{ $report->scanResult?->website ? ' pada Website '.($report->scanResult->website->opd_name ?? $report->scanResult->website->website_name) : '' }}</strong></td>
            <td></td>
        </tr>
    </table>

    <div class="recipient">
        <div class="to-label">Yth.</div>
        <div class="to-name">{{ $report->scanResult->website->admin_name ?? 'Pengelola Website' }}</div>
        <div>{{ $report->scanResult->website->opd_name ?? '' }}</div>
        <div>di Tempat</div>
    </div>

    <p>Dengan hormat,</p>

    <p>
        Berdasarkan hasil pemantauan dan pemeriksaan yang dilakukan oleh {{ $setting->instansi_name ?? config('app.name', 'SIPAJUDOL') }}
        @if ($report->scanResult)
            pada tanggal {{ $report->scanResult->scan_date->translatedFormat('d F Y') }} terhadap website resmi
            <strong>{{ $report->scanResult->website->opd_name ?? $report->scanResult->website->website_name ?? '-' }}</strong> ({{ $report->scanResult->website->domain ?? '-' }}),
        @else
            ,
        @endif
        ditemukan indikasi keberadaan konten dan/atau tautan yang mengarah ke situs judi online sebagaimana diuraikan pada laporan Nomor {{ $report->report_number }} berikut ini.
    </p>

    @if ($report->scanResult)
        <div class="section-title">Data Hasil Pemindaian</div>
        <table class="data">
            <tr><th style="width: 30%;">Skor Risiko</th><td>{{ $report->scanResult->risk_score }} / 100</td></tr>
            <tr><th>Jenis Ancaman</th><td>{{ $report->scanResult->threat_type ?: '-' }}</td></tr>
            <tr><th>Jumlah Kata Kunci Terdeteksi</th><td>{{ $report->scanResult->keyword_count }}</td></tr>
            <tr><th>Jumlah Tautan Judi Online</th><td>{{ $report->scanResult->judol_link_count }}</td></tr>
            <tr><th>Halaman Terindikasi</th><td>{{ $report->scanResult->infected_pages }}</td></tr>
        </table>

        @if ($report->scanResult->findings->isNotEmpty())
            <div class="section-title">Rincian Temuan</div>
            <table class="data">
                <thead>
                    <tr>
                        <th style="width: 18%">Kategori</th>
                        <th style="width: 12%">Tingkat</th>
                        <th style="width: 38%">Keterangan</th>
                        <th style="width: 32%">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report->scanResult->findings as $finding)
                        <tr>
                            <td>{{ $finding->category }}</td>
                            <td>
                                <span class="badge {{ match($finding->severity) { 'critical', 'high' => 'badge-danger', 'medium' => 'badge-warning', default => 'badge-slate' } }}">
                                    {{ ucfirst($finding->severity) }}
                                </span>
                            </td>
                            <td>{{ $finding->message }}</td>
                            <td>{{ $finding->evidence ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    @if ($report->summary)
        <div class="section-title">Ringkasan Temuan</div>
        <p class="body-text">{{ $report->summary }}</p>
    @endif

    @if ($report->conclusion)
        <div class="section-title">Kesimpulan</div>
        <p class="body-text">{{ $report->conclusion }}</p>
    @endif

    <div class="section-title">Rekomendasi Tindak Lanjut</div>
    <p class="body-text">{{ $report->recommendation ?: 'Segera membersihkan konten dan tautan yang terindikasi, serta memperkuat keamanan sistem website.' }}</p>

    <p class="closing">
        Sehubungan dengan hal tersebut, kami mohon agar Saudara/i segera menindaklanjuti temuan ini demi menjaga kredibilitas dan keamanan layanan informasi publik. Demikian pemberitahuan ini disampaikan, atas perhatian dan kerja sama yang baik diucapkan terima kasih.
    </p>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <div class="signature-block">
                    <div class="role">{{ $setting->head_name ? 'Kepala Bidang' : 'Analis / Petugas Pemeriksa' }},</div>
                    <div class="name">{{ $setting->head_name ?? $report->analyst }}</div>
                    @if ($setting->nip ?? null)
                        <div class="nip">NIP. {{ $setting->nip }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="tembusan">
        <div class="title">Tembusan:</div>
        <div>1. {{ $report->scanResult->website->opd_name ?? 'OPD terkait' }} (sebagai laporan)</div>
        <div>2. Arsip</div>
    </div>
</body>
</html>
