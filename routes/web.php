<?php

use App\Http\Controllers\InfoController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SaveImageController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Middleware\EnsureClassOwner;

Route::get('lang', [LanguageController::class, 'change'])->name('change.lang');

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('/api')->group(function () {
        Route::post('/upload-image', [SaveImageController::class, 'upload'])->name('upload-image');
        Route::post('/upload-file', [SaveImageController::class, 'uploadFile'])->name('upload-file');
        Route::get('/info', [InfoController::class, 'getInfoUrl'])->name('info');
    });


    Route::redirect('settings', 'settings/profile');

    Volt::route('app', 'app-page')->name('my-app');
    Volt::route('classroom', 'classroom')->name('classroom');
    Volt::route('classroom/{id}', 'classroom-learn')->name('classroom-learn');

    Route::middleware([EnsureClassOwner::class])->group(function () {
        Volt::route('classroom/{id}/add/{task}', 'task-add')->name('task-add');
    });


    Volt::route('chat', 'chat')->name('chat');


    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
