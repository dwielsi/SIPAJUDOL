<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Website extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'opd_name',
        'website_name',
        'domain',
        'subdomain',
        'ip_server',
        'hosting',
        'cms',
        'cms_version',
        'server_location',
        'admin_name',
        'admin_email',
        'admin_phone',
        'status',
        'scan_failure_reason',
        'notes',
        'last_scanned_at',
        'response_time_ms',
        'ssl_valid',
        'ssl_expires_at',
        'latest_risk_score',
        'baseline_hash',
    ];

    protected function casts(): array
    {
        return [
            'last_scanned_at' => 'datetime',
            'ssl_expires_at' => 'datetime',
            'ssl_valid' => 'boolean',
        ];
    }

    /**
     * Samakan format domain (tanpa skema http/https, tanpa garis miring di akhir, huruf kecil)
     * agar validasi unik dan penyimpanan memakai nilai yang sama.
     */
    public static function normalizeDomain(?string $domain): ?string
    {
        if ($domain === null || trim($domain) === '') {
            return $domain;
        }

        return strtolower(rtrim(preg_replace('#^https?://#i', '', trim($domain)), '/'));
    }

    /**
     * Nama untuk narasi analisis, mis. "Website Dinas Sosial". Bila nama website hanya berupa
     * domain, nama OPD yang dipakai agar kalimat lebih mudah dibaca.
     */
    public function displayName(): string
    {
        $name = $this->website_name;

        if ((blank($name) || $name === $this->domain) && filled($this->opd_name)) {
            $name = $this->opd_name;
        }

        $name = $name ?: $this->domain;

        return str_starts_with(strtolower($name), 'website') ? $name : "Website {$name}";
    }

    public function scanResults()
    {
        return $this->hasMany(ScanResult::class);
    }

    /**
     * Website yang gagal dipindai tidak boleh tetap berstatus aman: status diganti menjadi
     * "Gagal Dipindai" beserta alasannya sampai pemindaian berikutnya berhasil.
     */
    public function markScanFailed(string $reason): void
    {
        $this->update([
            'status' => 'scan_failed',
            'scan_failure_reason' => $reason,
            'last_scanned_at' => now(),
        ]);
    }

    /**
     * Website sedang dipindai bila memiliki scan yang masih mengantre/berjalan. Gunakan
     * withScanningState() saat memuat banyak website agar tidak terjadi query berulang.
     */
    public function isScanning(): bool
    {
        return (bool) ($this->is_scanning ?? $this->scanResults()->inProgress()->exists());
    }

    public function scopeWithScanningState($query)
    {
        return $query->withExists(['scanResults as is_scanning' => fn ($scan) => $scan->inProgress()]);
    }

    public function statusLabel(): string
    {
        if ($this->isScanning()) {
            return 'Sedang Dipindai';
        }

        return match ($this->status) {
            'safe' => 'Aman',
            'needs_review' => 'Perlu Pemeriksaan',
            'flagged' => 'Terindikasi',
            'scan_failed' => 'Gagal Dipindai',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        if ($this->isScanning()) {
            return 'primary';
        }

        return match ($this->status) {
            'safe' => 'success',
            'needs_review' => 'warning',
            'flagged' => 'danger',
            default => 'slate',
        };
    }

    public function riskDotColor(): string
    {
        return match (true) {
            $this->status === 'flagged' => 'bg-danger-500',
            $this->status === 'needs_review' => 'bg-warning-500',
            $this->status === 'scan_failed' => 'bg-slate-400',
            default => 'bg-success-500',
        };
    }
}
