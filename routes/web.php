<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpellingWords;
use App\Http\Controllers\WordManager;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    // write a loop to print all env variables
    $user = auth()->user();
    if($user)   {
        $in24Hours = (new DateTime())->modify('+24 hours');
        $token = $user->createToken('auth-token', expiresAt: $in24Hours )->plainTextToken;
    }

    return view('welcome', compact('user', 'token'));

});

Route::get('/spelling-test', [SpellingWords::class, 'showSpellingTest'])->name('spelling-test');

Route::get('/word-manager', [WordManager::class, 'show_word_manager'])->name('word-manager');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/spelling-test', [SpellingWords::class, 'showSpellingTest'])->name('spelling-test');

    Route::get('/word-manager', [WordManager::class, 'show_word_manager'])->name('word-manager');
});

// pull in the routes from the jetstream package that we moved in so we could safely modify them
require_once __DIR__.'/jetstream.php';
require_once __DIR__.'/fortify.php';
