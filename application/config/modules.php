<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modules Path
    |--------------------------------------------------------------------------
    |
    | This value determines the directory that hosts all of the panel modules.
    | Each sub-directory must contain a `module.php` manifest file.
    |
    */

    'path' => app_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Route Options
    |--------------------------------------------------------------------------
    |
    | Module route files (declared via the `routes` key in the manifest) are
    | registered inside this prefix and middleware group. By default they
    | share the admin prefix and the web+auth+verified middleware, but you
    | can override it here for a whole module inside its own route file by
    | using its own Route::group().
    |
    */

    'prefix' => env('MODULES_PREFIX'),

    'middleware' => ['web', 'auth', 'verified'],

];
