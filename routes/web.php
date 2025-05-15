<?php

use App\Events\NotificationsEvent;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SaveImageController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Middleware\EnsureClassOwner;

Route::get('lang', [LanguageController::class, 'change'])->name('change.lang');

// Route::post('/send-notification', [NotificationController::class, 'sendNotification'])->name('notifications');

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::middleware(['csrf'])->group(function () {
    Route::get('/api/get-name-school', [InfoController::class, 'getSchool'])->name('name-school');
});

Volt::route('not-found', 'error.not-found')->name('not-found');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('/api')->group(function () {
        Route::post('/upload-image', [SaveImageController::class, 'upload'])->name('upload-image');
        Route::post('/upload-file', [SaveImageController::class, 'uploadFile'])->name('upload-file');
        Route::get('/info', [InfoController::class, 'getInfoUrl'])->name('info');
    });

    Route::redirect('settings', 'settings/profile');

    Volt::route('app', 'app-page')->name('my-app');
    Volt::route('classroom', 'classroom')->name('classroom');

    Route::middleware(['member-classroom'])->group(function () {
        Volt::route('classroom/{id}', 'classroom-learn')->name('classroom-learn');
        Volt::route('classroom/{id}/task/{task}', 'classroom-task')->name('classroom-task');
        Volt::route('classroom/{id}/read/{task}', 'classroom-read')->name('classroom-read');
        
        Route::middleware([EnsureClassOwner::class])->group(function () {
            Volt::route('classroom/{id}/add/{task}', 'task-add')->name('task-add');
        });
    });

    Route::middleware(['admin'])->group(function () {
        Volt::route('admin', 'admin.dashboard')->name('admin');
        Volt::route('admin/aplication', 'admin.aplication')->name('admin.aplication');
        Volt::route('admin/default-avatar', 'admin.default-avatar')->name('admin.avatar');
    });

    Volt::route('chat', 'chat')->name('chat');

    // Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/profile', 'settings.my-profile')->name('settings.profile');

    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';

Volt::route('not-found', 'error.not-found')->name('not-found');
Route::fallback(function () {
    return redirect()->route('not-found')->with('error', __('error.not_found'))->with('code', 404);
});
