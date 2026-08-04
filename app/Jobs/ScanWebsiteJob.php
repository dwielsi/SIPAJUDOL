<?php

namespace App\Jobs;

use App\Models\ScanResult;
use App\Models\Website;
use App\Services\Scanner\WebsiteScannerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScanWebsiteJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 300;

    public function __construct(
        public readonly Website $website,
        public readonly ScanResult $scanResult,
    ) {}

    public function handle(WebsiteScannerService $scanner): void
    {
        $scanner->run($this->website, $this->scanResult);
    }
}
