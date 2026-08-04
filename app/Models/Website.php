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

    public function scanResults()
    {
        return $this->hasMany(ScanResult::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'safe' => 'Aman',
            'needs_review' => 'Perlu Pemeriksaan',
            'flagged' => 'Terindikasi',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
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
            default => 'bg-success-500',
        };
    }
}

