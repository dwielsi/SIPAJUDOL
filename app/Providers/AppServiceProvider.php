<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Keyword;
use App\Models\Report;
use App\Models\ScanResult;
use App\Models\Website;
use App\Observers\ReportObserver;
use App\Observers\ScanResultObserver;
use App\Observers\WebsiteObserver;
use App\Policies\KeywordPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ScanResultPolicy;
use App\Policies\WebsitePolicy;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use App\Repositories\ReportRepository;
use App\Repositories\WebsiteRepository;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WebsiteRepositoryInterface::class, WebsiteRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Website::class, WebsitePolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(ScanResult::class, ScanResultPolicy::class);
        Gate::policy(Keyword::class, KeywordPolicy::class);

        Website::observe(WebsiteObserver::class);
        Report::observe(ReportObserver::class);
        ScanResult::observe(ScanResultObserver::class);

        Event::listen(function (Login $event) {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'auth.login',
                'subject_type' => $event->user::class,
                'subject_id' => $event->user->id,
                'description' => "{$event->user->name} masuk ke sistem",
                'ip_address' => request()->ip(),
            ]);
        });

        Event::listen(function (Logout $event) {
            if (! $event->user) {
                return;
            }

            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'auth.logout',
                'subject_type' => $event->user::class,
                'subject_id' => $event->user->id,
                'description' => "{$event->user->name} keluar dari sistem",
                'ip_address' => request()->ip(),
            ]);
        });
    }
}
