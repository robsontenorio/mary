<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->get('/mary/toogle-sidebar', function (Request $request) {
    if ($request->collapsed) {
        session(['mary-sidebar-collapsed' => $request->collapsed]);
    }
});

Route::middleware('web')->get('/mary/spotlight', function (Request $request) {
    return app()->make(config('mary.components.spotlight.class'))->search($request);
});
