<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/mary/toogle-sidebar', function (Request $request) {
    if ($request->collapsed) {
        cache(['mary-sidebar-collapsed' => $request->collapsed]);
    }
});

Route::middleware('cache.headers:public;max_age=2628000;etag')->get('/mary/asset', function (Request $request) {
    if (! $request->name) {
        abort(404);
    }

    if (Str::of($request->name)->contains('..')) {
        abort(404);
    }

    $file = Str::of($request->name)->before('?')->toString();

    if (! File::exists(__DIR__ . "/../libs/{$file}")) {
        abort(404);
    }

    $extension = Str::of($file)->afterLast('.')->toString();

    $type = match ($extension) {
        'js' => 'application/javascript',
        'css' => 'text/css',
        default => 'text/html'
    };

    return Cache::rememberForever($request->name, function () use ($file, $type, $request) {
        return response(File::get(__DIR__ . "/../libs/{$file}"))->withHeaders([
            'Content-Type' => $type,
        ]);
    });
});
