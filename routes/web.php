<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

if(config('mary.routes.toggle_sidebar.enabled')) {
    Route::middleware('web')->prefix(config('mary.route_prefix'))->get('/mary/toggle-sidebar', function (Request $request) {
        if ($request->collapsed) {
            session(['mary-sidebar-collapsed' => $request->collapsed]);
        }
    })->name('mary.toggle-sidebar');
}

if(config('mary.routes.spotlight.enabled')) {
    Route::middleware('web')->prefix(config('mary.route_prefix'))->get('/mary/spotlight', function (Request $request) {
        return app()->make(config('mary.components.spotlight.class'))->search($request);
    })->name('mary.spotlight');
}

if(config('mary.routes.upload.enabled')) {
    Route::middleware(['web', 'auth'])->prefix(config('mary.route_prefix'))->post('/mary/upload', function (Request $request) {
        $disk = $request->disk ?? 'public';
        $folder = $request->folder ?? 'editor';

        $file = Storage::disk($disk)->put($folder, $request->file('file'), 'public');
        $url = Storage::disk($disk)->url($file);

        return ['location' => $url];
    })->name('mary.upload');
}
