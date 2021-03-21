<?php

use Illuminate\Support\Facades\Route;
use JulesGraus\FaviconTracker\Tracker;

Route::group(['prefix' => config('favicontracker.prefix'), 'middleware' => []], function() {
    Route::get('/', [Tracker::class, 'action'])->name('fit.action');
    Route::get('/write', [Tracker::class, 'write'])->name('fit.write');
    Route::get('/read', [Tracker::class, 'read'])->name('fit.read');
    Route::get('/{path}/index', [Tracker::class, 'loop'])->name('fit.loop');
    Route::get('/done/', [Tracker::class, 'done'])->name('fit.done');
    Route::get('/{path}/favicon.png', [Tracker::class, 'favicon'])->name('fit.favicon');
});