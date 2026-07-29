<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('/share/{token}', 'pages::media.share')->name('media.share.public');

Route::get('/locale/{lang}', function (string $lang) {
    $locales = config('app.available_locales', []);
    if (array_key_exists($lang, $locales)) {
        session(['locale' => $lang]);
        if (auth()->check()) {
            auth()->user()->update(['locale' => $lang]);
        }
    }
    return redirect()->back();
})->name('locale.switch');

$adminPrefix = config('fortify.prefix', 'adminx');

// Fallback redirects for root paths to admin prefix
if ($adminPrefix) {
    Route::redirect('/dashboard', '/' . $adminPrefix . '/dashboard');
    Route::redirect('/login', '/' . $adminPrefix . '/login');
    Route::redirect('/register', '/' . $adminPrefix . '/register');
    Route::redirect('/forgot-password', '/' . $adminPrefix . '/forgot-password');
}

Route::prefix($adminPrefix)->middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('users', 'pages::users.index')->name('users.index');
    Route::livewire('roles', 'pages::roles.index')->name('roles.index');
    Route::livewire('settings/system', 'pages::settings.system')->name('settings.system');
    Route::livewire('settings/languages', 'pages::settings.languages')->name('settings.languages');
    Route::livewire('settings/logs', 'pages::settings.logs')->name('settings.logs');
    Route::livewire('settings/backups', 'pages::settings.backups')->name('settings.backups');
    Route::livewire('media', 'pages::media.index')->name('media.index');
    Route::get('/media/file/{filename}', function (string $filename) {
        $path = "media/{$filename}";
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($path));
        }
        abort(404);
    })->name('media.file');
});

require __DIR__.'/settings.php';
