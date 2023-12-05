<?php

use App\Http\Controllers\WordManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bank;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/update-points', function (Request $request) {
    return Bank::updatePoints($request, $request->input('user_id'));
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/update-points', function (Request $request) {
        return Bank::updatePoints($request, $request->input('user_id'));
    });

    Route::post('/remove-words', function (Request $request) {
        return WordManager::deactivate_words($request);
    })->name('wordmanager.deactivate');

    Route::post('/add-words', function (Request $request) {
        return WordManager::add_words($request);
    })->name('wordmanager.add');
});

