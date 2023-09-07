<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/mary/toogle-sidebar', function (Request $request) {
    if ($request->collapsed) {
        cache(['mary-sidebar-collapsed' => $request->collapsed]);
    }
});
