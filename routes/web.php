<?php

use App\Http\Controllers\Tweets\IndexController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/api/tweets', [IndexController::class, '__invoke'])->name('tweets.index');

Route::get('/', function () {
    return Inertia::render('TweetsFeed');
});
