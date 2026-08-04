<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true]);
        }
    }

    public function typeIcon(): string
    {
        return match ($this->type) {
            'threat' => '🔴',
            'warning' => '🟡',
            'success' => '🟢',
            default => 'ℹ️',
        };
    }

    public function typeColor(): string
    {
        return match ($this->type) {
            'threat' => 'danger',
            'warning' => 'warning',
            'success' => 'success',
            default => 'primary',
        };
    }
}
