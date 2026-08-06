<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scan_result_id',
        'report_number',
        'report_date',
        'analyst',
        'summary',
        'conclusion',
        'recommendation',
        'status',
        'pdf_path',
        'qr_code_path',
        'sent_at',
        'sent_to',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function scanResult()
    {
        return $this->belongsTo(ScanResult::class);
    }
}
