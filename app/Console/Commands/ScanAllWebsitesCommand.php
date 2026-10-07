<?php

namespace App\Console\Commands;

use App\Jobs\ScanWebsiteJob;
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
            ScanWebsiteJob::start($website);
        }

        $this->info("Dispatched scan jobs for {$websites->count()} website(s).");

        return self::SUCCESS;
    }
}
