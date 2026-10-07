<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'ip_address',
    ];

    public const ACTION_LABELS = [
        'auth.login' => 'Masuk',
        'auth.logout' => 'Keluar',
        'website.created' => 'Tambah Website',
        'website.updated' => 'Ubah Website',
        'website.deleted' => 'Hapus Website',
        'scan.started' => 'Mulai Pemindaian',
        'scan.completed' => 'Pemindaian Selesai',
        'scan.failed' => 'Pemindaian Gagal',
        'report.created' => 'Buat Laporan',
        'report.generated' => 'Buat Laporan',
        'report.updated' => 'Ubah Laporan',
        'report.deleted' => 'Hapus Laporan',
        'report.sent' => 'Kirim Laporan',
    ];

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? str($this->action)->replace('.', ' ')->headline();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
