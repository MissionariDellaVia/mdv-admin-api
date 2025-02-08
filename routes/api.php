<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController,
    SaintController,
    GospelController,
    CommentController,
    GospelWayController,
    PlaceController,
    ContactController,
    EventController,
    TextContentController,
    MediaController,
    SeedController};


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'pong',
        'metadata' => [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'user' => 'Alessandro-Mac7'
        ]
    ]);
});

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
});

// Routes protected by tokens
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::prefix('mdv/v1')->group(function () {
        // Sacred Content Routes
        Route::apiResource('saints', SaintController::class);
        Route::apiResource('gospels', GospelController::class);
        Route::apiResource('comments', CommentController::class);
        Route::apiResource('gospel-way', GospelWayController::class);
        Route::get('seeds/random', [SeedController::class, 'random']);
        Route::apiResource('seeds', SeedController::class);

        Route::get('search/gospels', [GospelController::class, 'searchVerse']);
        Route::get('search/saints', [SaintController::class, 'searchName']);


        // Contact Management Routes
        Route::group(['prefix' => 'contacts'], function () {
            Route::apiResource('places', PlaceController::class);
        });

        Route::apiResource('contacts', ContactController::class);

        // Events Management
        Route::apiResource('events', EventController::class);

        // Content Management Routes
        Route::group(['prefix' => 'content'], function () {
            // Text Contents
            Route::get('pages/slug/{slug}', [TextContentController::class, 'findBySlug']);
            Route::apiResource('pages', TextContentController::class);

            // Media Library
            Route::apiResource('media', MediaController::class);
        });

        Route::get('bff/gospel-way', [GospelWayController::class, 'getCompleteGospelWay'])
            ->name('api.bff.gospel-way');

    });
});
