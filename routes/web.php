<?php

use App\Http\Controllers\Tweets\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('api/tweets', [IndexController::class, '__invoke'])->name('tweets.index');
