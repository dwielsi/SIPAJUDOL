<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function notify(string $title, string $message, string $type = 'info'): Notification
    {
        return Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }
}
