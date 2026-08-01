<?php

use Illuminate\Support\Facades\Route;

// This file is loaded inside the admin prefix with the
// web + auth + verified middleware group (see ModuleBootstrapper).
//
// Component names use the "exca-coin::" namespace which is registered
// in ModuleBootstrapper and maps to App\Modules\ExcaCoin\Livewire\Pages.

Route::livewire('quick-entry', 'exca-coin::quick-entry')->name('quick-entry.index');

Route::livewire('dictionaries', 'exca-coin::dictionaries')->name('dictionaries.index');

Route::livewire('excavation-projects', 'exca-coin::excavation-projects')->name('excavation-projects.index');

Route::livewire('finds', 'exca-coin::finds')->name('all-finds.index');
Route::livewire('finds/create', 'exca-coin::finds.create')->name('all-finds.create');
Route::livewire('excavation-projects/{project}/finds', 'exca-coin::finds')->name('finds.index');
Route::livewire('excavation-projects/{project}/finds/create', 'exca-coin::finds.create')->name('finds.create');
Route::livewire('excavation-projects/{project}/finds/{find}/edit', 'exca-coin::finds.edit')->name('finds.edit');

Route::livewire('coins', 'exca-coin::coins')->name('all-coins.index');
Route::livewire('coins/create', 'exca-coin::coins.create')->name('all-coins.create');
Route::livewire('excavation-projects/{project}/finds/{find}/coins', 'exca-coin::coins')->name('coins.index');
Route::livewire('excavation-projects/{project}/finds/{find}/coins/create', 'exca-coin::coins.create')->name('coins.create');
Route::livewire('excavation-projects/{project}/finds/{find}/coins/{coin}/edit', 'exca-coin::coins.edit')->name('coins.edit');
Route::livewire('excavation-projects/{project}/finds/{find}/coins/{coin}', 'exca-coin::coins.show')->name('coins.show');
