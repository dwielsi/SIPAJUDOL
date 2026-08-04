<?php

namespace App\Console\Commands;

use App\Jobs\ScanWebsiteJob;
use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Console\Command;

class ScanAllWebsitesCommand extends Command
{
    protected $signature = 'scanner:run-all';

    protected $description = 'Dispatch a scan job for every registered website';

    public function handle(): int
    {
        $websites = Website::all();

        foreach ($websites as $website) {
            $scanResult = ScanResult::create([
                'website_id' => $website->id,
                'scan_date' => now(),
                'scan_state' => 'queued',
            ]);

            ScanWebsiteJob::dispatch($website, $scanResult);
        }

        $this->info("Dispatched scan jobs for {$websites->count()} website(s).");

        return self::SUCCESS;
    }
}
