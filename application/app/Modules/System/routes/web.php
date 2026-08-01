<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// This file is loaded inside the admin prefix with the
// auth + verified middleware group (see ModuleBootstrapper).

Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

Route::livewire('users', 'pages::users.index')->name('users.index');
Route::livewire('roles', 'pages::roles.index')->name('roles.index');

Route::livewire('settings/system', 'pages::settings.system')->name('settings.system');
Route::livewire('settings/languages', 'pages::settings.languages')->name('settings.languages');
Route::livewire('settings/logs', 'pages::settings.logs')->name('settings.logs');
Route::livewire('settings/system-info', 'pages::settings.system-info')->name('settings.system-info');
Route::livewire('settings/backups', 'pages::settings.backups')->name('settings.backups');

Route::livewire('media', 'pages::media.index')->name('media.index');

Route::get('media/file/{filename}', function (string $filename) {
    abort_unless(auth()->user()->can('media.view') || auth()->user()->hasRole('super-admin'), 403);

    $path = "media/{$filename}";
    if (Storage::disk('local')->exists($path)) {
        return response()->file(Storage::disk('local')->path($path));
    }
    abort(404);
})->name('media.file');

Route::post('settings/cache/clear-all', function () {
    abort_unless(auth()->user()->can('settings.update') || auth()->user()->hasRole('super-admin'), 403);
    Artisan::call('config:clear');
    Artisan::call('route:clear');

    return redirect()->back();
})->name('cache.clear-all');
