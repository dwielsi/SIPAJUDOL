<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScanProgressController;
use App\Http\Controllers\ScanResultController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('websites', WebsiteController::class);

    Route::get('reports/{report}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::post('reports/{report}/send', [ReportController::class, 'send'])->name('reports.send');
    Route::resource('reports', ReportController::class);

    Route::resource('scan-results', ScanResultController::class)->except(['edit', 'update']);
    Route::get('scan-results/{scanResult}/status', [ScanProgressController::class, 'status'])->name('scan-results.status');

    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

    Route::get('/spk', [SpkController::class, 'index'])->name('spk.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::resource('keywords', KeywordController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-email', [SettingController::class, 'testEmail'])->name('settings.test-email');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
