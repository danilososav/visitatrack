<?php

use App\Http\Controllers\VisitFileController;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\Login;
use App\Livewire\Portal\VisitWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('portal.login');
    }

    return Auth::user()->role === 'admin'
        ? redirect('/admin')
        : redirect()->route('portal.dashboard');
});

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', Login::class)->middleware('guest')->name('login');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('portal.login');
    })->middleware('auth')->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/visits/create', VisitWizard::class)->name('visits.create');
        Route::get('/visits/{visit}', VisitWizard::class)->name('visits.show');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/files/visits/photos/{photo}', [VisitFileController::class, 'photo'])
        ->name('files.visit-photo');

    Route::get('/files/visits/{visit}/signature/{who}', [VisitFileController::class, 'signature'])
        ->whereIn('who', ['worker', 'second'])
        ->name('files.visit-signature');
});
