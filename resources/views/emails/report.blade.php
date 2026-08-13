<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report->report_number }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family: Arial, Helvetica, sans-serif; color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#0f172a; padding:20px 28px;">
                            <p style="margin:0; color:#ffffff; font-size:14px; font-weight:bold;">{{ $setting->instansi_name ?? config('app.name', 'SIDEPSIL') }}</p>
                            <p style="margin:2px 0 0; color:#94a3b8; font-size:12px;">Sistem Informasi Deteksi dan Pelaporan Penyisipan Konten Ilegal</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">Yth. Bapak/Ibu Pengelola Website,</p>

                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                Bersama ini kami sampaikan laporan hasil pemindaian dan analisis atas indikasi konten perjudian online
                                pada website yang berada di bawah pengelolaan instansi Bapak/Ibu. Detail temuan terlampir dalam
                                dokumen PDF pada surel ini.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; margin-bottom:16px;">
                                <tr>
                                    <td style="padding:4px 0; color:#64748b; width:160px;">Nomor Laporan</td>
                                    <td style="padding:4px 0; font-weight:bold;">{{ $report->report_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0; color:#64748b;">Tanggal Laporan</td>
                                    <td style="padding:4px 0;">{{ $report->report_date->translatedFormat('d M Y') }}</td>
                                </tr>
                                @if ($report->scanResult?->website)
                                    <tr>
                                        <td style="padding:4px 0; color:#64748b;">Website</td>
                                        <td style="padding:4px 0;">{{ $report->scanResult->website->website_name }} ({{ $report->scanResult->website->domain }})</td>
                                    </tr>
                                @endif
                            </table>

                            @if ($note)
                                <p style="margin:0 0 16px; font-size:14px; line-height:1.6; white-space:pre-line;">{{ $note }}</p>
                            @endif

                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                Kami mohon agar temuan ini dapat segera ditindaklanjuti sesuai rekomendasi yang tercantum pada laporan
                                terlampir. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
                            </p>

                            <p style="margin:24px 0 0; font-size:14px; line-height:1.6;">
                                Hormat kami,<br>
                                <strong>{{ $setting->head_name ?? $report->analyst }}</strong><br>
                                @if ($setting->nip ?? null)
                                    NIP. {{ $setting->nip }}<br>
                                @endif
                                {{ $setting->instansi_name ?? config('app.name', 'SIDEPSIL') }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f8fafc; padding:16px 28px; font-size:11px; color:#94a3b8;">
                            Surel ini dikirim otomatis oleh {{ config('app.name', 'SIDEPSIL') }}. Mohon tidak membalas surel ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
