<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/mary/toogle-sidebar', function (Request $request) {
    if ($request->collapsed) {
        cache(['mary-sidebar-collapsed' => $request->collapsed]);
    }
});

Route::get('/mary/asset', function (Request $request) {
    if ($request->name) {
        $extension = Str::of($request->name)->afterLast('.')->toString();

        $type = match ($extension) {
            'js' => 'application/javascript',
            'css' => 'text/css',
            default => 'text/html'
        };

        return response(File::get(__DIR__ . "/../libs/{$request->name}"))->header('Content-Type', $type);
    }

    abort(404);
});
