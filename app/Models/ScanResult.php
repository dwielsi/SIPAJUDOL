<?php

namespace App\Models;

use App\Jobs\ScanWebsiteJob;
use App\Services\QueueWorkerService;
use Database\Factories\ScanResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScanResult extends Model
{
    /** @use HasFactory<ScanResultFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'website_id',
        'scan_date',
        'status',
        'failure_reason',
        'risk_score',
        'threat_type',
        'judol_link_count',
        'infected_pages',
        'screenshot_path',
        'findings_summary',
        'details',
        'notes',
        'scan_state',
        'progress_percent',
        'current_step',
        'started_at',
        'completed_at',
        'redirect_count',
        'malware_count',
        'external_link_count',
        'ai_summary',
        'ai_conclusion',
        'ai_recommendation',
        'ai_priority',
    ];

    /**
     * Kategori temuan yang dihitung ke tiap kolom ringkasan hasil scan.
     * Iframe ke domain eksternal termasuk sisipan berbahaya, bukan konten ilegal.
     */
    public const COUNT_CATEGORIES = [
        'judol_link_count' => ['illegal_content', 'hidden_link', 'seo_spam', 'meta_tag_spam'],
        'redirect_count' => ['redirect'],
        'malware_count' => [
            'malware_signature', 'backdoor', 'php_shell', 'foreign_file', 'iframe',
            'script_injection', 'js_injection', 'eval_base64', 'defacement',
        ],
        'external_link_count' => ['external_link'],
    ];

    /**
     * @param  iterable<object{category: string}>  $findings
     * @return array<string, int>
     */
    public static function countsFromFindings(iterable $findings): array
    {
        $categories = collect($findings)->pluck('category');

        return collect(self::COUNT_CATEGORIES)
            ->map(fn (array $group) => $categories->filter(fn ($c) => in_array($c, $group, true))->count())
            ->all();
    }

    protected function casts(): array
    {
        return [
            'scan_date' => 'date',
            'details' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function findings()
    {
        return $this->hasMany(ScanFinding::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('scan_state', ['queued', 'running']);
    }

    public function markFailed(string $reason): void
    {
        $this->update([
            'scan_state' => 'failed',
            'current_step' => Str::limit($reason, 250),
            'failure_reason' => $reason,
            'completed_at' => now(),
        ]);
    }

    /**
     * Menandai gagal scan yang macet: sedang berjalan tapi tidak ada progres, atau masih
     * mengantre padahal tidak ada worker yang aktif memproses antrean.
     */
    public static function expireStale(): void
    {
        $cutoff = now()->subSeconds(ScanWebsiteJob::TIMEOUT_BUFFER_SECONDS);

        static::query()
            ->where('scan_state', 'running')
            ->where('updated_at', '<', $cutoff)
            ->get()
            ->each->markFailed('Pemindaian dihentikan otomatis karena tidak ada progres (proses macet atau terhenti).');

        $queueIsMoving = app(QueueWorkerService::class)->isRunning()
            || static::query()->where('started_at', '>=', $cutoff)->exists();

        if ($queueIsMoving) {
            return;
        }

        static::query()
            ->where('scan_state', 'queued')
            ->where('updated_at', '<', $cutoff)
            ->get()
            ->each->markFailed('Pemindaian dibatalkan otomatis karena antrean tidak diproses. Silakan pindai ulang.');

        if (static::query()->where('scan_state', 'queued')->exists()) {
            app(QueueWorkerService::class)->ensureRunning();
        }
    }

    public function riskLevelLabel(): string
    {
        if (in_array($this->scan_state, ['queued', 'running'], true)) {
            return 'Menunggu Hasil';
        }

        if ($this->scan_state === 'failed') {
            return 'Gagal Dipindai';
        }

        return match ($this->status) {
            'flagged' => 'Terindikasi',
            'needs_review' => 'Perlu Pemeriksaan',
            default => 'Aman',
        };
    }

    public function riskLevelColor(): string
    {
        if (in_array($this->scan_state, ['queued', 'running', 'failed'], true)) {
            return 'slate';
        }

        return match ($this->status) {
            'flagged' => 'danger',
            'needs_review' => 'warning',
            default => 'success',
        };
    }

    public function screenshotUrl(): ?string
    {
        if (! $this->screenshot_path || ! Storage::disk('public')->exists($this->screenshot_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->screenshot_path);
    }
}
