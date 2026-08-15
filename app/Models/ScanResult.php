<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ScanResult extends Model
{
    /** @use HasFactory<\Database\Factories\ScanResultFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'website_id',
        'scan_date',
        'status',
        'risk_score',
        'threat_type',
        'keyword_count',
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

    public function riskLevelLabel(): string
    {
        return match ($this->status) {
            'flagged' => 'Terindikasi',
            'needs_review' => 'Perlu Pemeriksaan',
            default => 'Aman',
        };
    }

    public function riskLevelColor(): string
    {
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
