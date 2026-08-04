<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Website;

class WebsiteObserver
{
    public function created(Website $website): void
    {
        $this->log('website.created', $website, "Menambahkan website {$website->domain}");
    }

    public function updated(Website $website): void
    {
        $this->log('website.updated', $website, "Memperbarui website {$website->domain}");
    }

    public function deleted(Website $website): void
    {
        $this->log('website.deleted', $website, "Menghapus website {$website->domain}");
    }

    private function log(string $action, Website $website, string $description): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => Website::class,
            'subject_id' => $website->id,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
