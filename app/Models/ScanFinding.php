<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanFinding extends Model
{
    /** @use HasFactory<\Database\Factories\ScanFindingFactory> */
    use HasFactory;

    protected $fillable = [
        'scan_result_id',
        'category',
        'severity',
        'message',
        'evidence',
        'page_url',
        'location',
    ];

    public function scanResult()
    {
        return $this->belongsTo(ScanResult::class);
    }

    public function severityColor(): string
    {
        return match ($this->severity) {
            'critical', 'high' => 'danger',
            'medium' => 'warning',
            default => 'slate',
        };
    }
}
