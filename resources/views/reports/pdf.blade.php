<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->report_number }}</title>
    <style>
        @page { margin: 150px 60px 70px 60px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #1f2937; line-height: 1.6; }
        header { position: fixed; top: -130px; left: 0; right: 0; height: 112px; font-family: 'Times New Roman', Times, serif; color: #000; }
        header table.kop { width: 100%; border-collapse: collapse; }
        header td.logo { width: 80px; vertical-align: middle; text-align: left; }
        header td.logo img { width: 66px; height: auto; }
        header td.text { text-align: center; vertical-align: middle; padding-right: 30px; }
        header .pemda { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; }
        header .dinas { font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.2; }
        header .alamat { font-size: 10.5px; font-weight: bold; }
        header table.kota { width: 100%; margin-top: 4px; }
        header table.kota td { font-size: 13px; }
        header .kota-name { font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-align: center; padding-left: 80px; }
        header .kodepos { width: 110px; text-align: right; font-style: italic; font-size: 11px; }
        header .rule-thick { border-top: 3px solid #000; margin-top: 3px; }
        header .rule-thin { border-top: 1px solid #000; margin-top: 1px; }
        .letter-date { text-align: right; margin-bottom: 14px; }
        footer { position: fixed; bottom: -50px; left: 0; right: 0; height: 40px; border-top: 1px solid #d1d5db; padding-top: 6px; font-size: 9px; color: #9ca3af; text-align: center; }
        table.letter-meta { width: 100%; margin-bottom: 10px; }
        table.letter-meta td { padding: 1px 0; vertical-align: top; font-size: 11px; }
        table.letter-meta td.label { width: 80px; }
        table.letter-meta td.colon { width: 10px; }
        .recipient { margin: 14px 0 16px; }
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
        .body-text { white-space: pre-line; text-align: left; }
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
    @php
        $logoPath = public_path('images/logo-kubu-raya.png');
        $logoSrc = is_file($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
    @endphp
    <header>
        <table class="kop">
            <tr>
                <td class="logo">
                    @if ($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo Kabupaten Kubu Raya">
                    @endif
                </td>
                <td class="text">
                    <div class="pemda">Pemerintah Kabupaten Kubu Raya</div>
                    <div class="dinas">Dinas Komunikasi dan Informatika</div>
                    <div class="alamat">Alamat : Jl. Supadio, Sungai Raya email : diskominfo@kuburayakab.go.id</div>
                </td>
            </tr>
        </table>
        <table class="kota">
            <tr>
                <td class="kota-name">Sungai Raya</td>
                <td class="kodepos">KodePos 78391</td>
            </tr>
        </table>
        <div class="rule-thick"></div>
        <div class="rule-thin"></div>
    </header>

    <footer>
        Dokumen ini dicetak melalui {{ config('app.name', 'SIDEPSIL') }} pada {{ now()->translatedFormat('d M Y, H:i') }} WIB
    </footer>

    <div class="letter-date">Sungai Raya, {{ $report->report_date->translatedFormat('d F Y') }}</div>

    <table class="letter-meta">
        <tr>
            <td class="label">Nomor</td>
            <td class="colon">:</td>
            <td>{{ $report->report_number }}</td>
            <td style="width: 40%;"></td>
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
            <td class="label">Hal</td>
            <td class="colon">:</td>
            <td><strong>Pemberitahuan Hasil Pemeriksaan Indikasi Konten Ilegal{{ $report->scanResult?->website ? ' pada Website '.($report->scanResult->website->opd_name ?? $report->scanResult->website->website_name) : '' }}</strong></td>
            <td></td>
        </tr>
    </table>

    <div class="recipient">
        <div>Yth. <span class="to-name">{{ $report->scanResult->website->admin_name ?? 'Pengelola Website' }}</span></div>
        @if ($report->scanResult?->website?->opd_name)
            <div style="padding-left: 26px;">{{ $report->scanResult->website->opd_name }}</div>
        @endif
        <div style="padding-left: 26px; margin-top: 8px;">di</div>
        <div style="padding-left: 40px;">Tempat</div>
    </div>

    <p>Dengan hormat,</p>

    <p>
        Berdasarkan hasil pemantauan dan pemeriksaan yang dilakukan oleh {{ $setting->instansi_name ?? config('app.name', 'SIDEPSIL') }}
        @if ($report->scanResult)
            pada tanggal {{ $report->scanResult->scan_date->translatedFormat('d F Y') }} terhadap website resmi
            <strong>{{ $report->scanResult->website->opd_name ?? $report->scanResult->website->website_name ?? '-' }}</strong> ({{ $report->scanResult->website->domain ?? '-' }}),
        @else
            ,
        @endif
        @if ($report->scanResult?->status === 'safe')
            tidak ditemukan indikasi keberadaan konten dan/atau tautan yang mengarah ke konten ilegal sebagaimana diuraikan pada laporan Nomor {{ $report->report_number }} berikut ini.
        @else
            ditemukan indikasi keberadaan konten dan/atau tautan yang mengarah ke konten ilegal sebagaimana diuraikan pada laporan Nomor {{ $report->report_number }} berikut ini.
        @endif
    </p>

    @if ($report->scanResult)
        <div class="section-title">Data Hasil Pemindaian</div>
        <table class="data">
            <tr><th style="width: 30%;">Skor Risiko</th><td>{{ $report->scanResult->risk_score }} / 100</td></tr>
            <tr><th>Jenis Ancaman</th><td>{{ $report->scanResult->threat_type ?: '-' }}</td></tr>
            <tr><th>Jumlah Tautan Konten Ilegal</th><td>{{ $report->scanResult->judol_link_count }}</td></tr>
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
        @if ($report->scanResult?->status === 'safe')
            Sehubungan dengan hal tersebut, kami mohon agar Saudara/i tetap menjaga keamanan website demi menjaga kredibilitas dan keamanan layanan informasi publik. Demikian pemberitahuan ini disampaikan, atas perhatian dan kerja sama yang baik diucapkan terima kasih.
        @else
            Sehubungan dengan hal tersebut, kami mohon agar Saudara/i segera menindaklanjuti temuan ini demi menjaga kredibilitas dan keamanan layanan informasi publik. Demikian pemberitahuan ini disampaikan, atas perhatian dan kerja sama yang baik diucapkan terima kasih.
        @endif
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
