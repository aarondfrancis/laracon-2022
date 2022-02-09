<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $inspire = function () {
        \Illuminate\Support\Facades\Artisan::call('inspire');

        return \Illuminate\Support\Facades\Artisan::output();
    };

    $results = \Hammerstone\Sidecar\PHP\LaravelLambda::executeMany([
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
        $inspire, $inspire, $inspire, $inspire, $inspire,
    ]);

    return collect($results)->map->body()->implode('<br>');
});
