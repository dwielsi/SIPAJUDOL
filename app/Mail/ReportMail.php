<?php

namespace App\Mail;

use App\Models\Report;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Report $report,
        public readonly ?string $note = null,
    ) {}

    public function envelope(): Envelope
    {
        $instansi = Setting::first()?->instansi_name ?: config('app.name');

        return new Envelope(
            subject: "Laporan Temuan Indikasi Judi Online - {$this->report->report_number} - {$instansi}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.report',
            with: [
                'report' => $this->report,
                'setting' => Setting::first(),
                'note' => $this->note,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->report->pdf_path || ! Storage::exists($this->report->pdf_path)) {
            return [];
        }

        $filename = str_replace('/', '-', $this->report->report_number);

        return [
            Attachment::fromStorage($this->report->pdf_path)
                ->as("{$filename}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
