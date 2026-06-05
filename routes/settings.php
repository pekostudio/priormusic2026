<?php

use App\Http\Controllers\Settings\BusinessController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ReportController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings/business', [BusinessController::class, 'edit'])->name('business.edit');
    Route::patch('settings/business', [BusinessController::class, 'update'])->name('business.update');

    Route::get('settings/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('settings/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('settings/reports/{musicUsageReport}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::delete('settings/reports/{musicUsageReport}', [ReportController::class, 'destroy'])->name('reports.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');
});
