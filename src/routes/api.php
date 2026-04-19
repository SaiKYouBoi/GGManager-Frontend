<?php
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\MatchController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::apiResource('tournaments', TournamentController::class)
    ->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tournaments', TournamentController::class)
        ->only(['store', 'update', 'destroy']);

    Route::patch('/matches/{match}/score', [MatchController::class, 'updateScore']);
});

Route::post('/tournaments/{tournament}/register', [RegistrationController::class, 'store'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tournaments/{tournament}/registrations', [RegistrationController::class, 'index']);
    Route::patch('/tournaments/{tournament}/close', [RegistrationController::class, 'close']);
});


Route::get('/tournaments/{tournament}/bracket', [TournamentController::class, 'bracket']);
Route::get('/tournaments/{tournament}', [TournamentController::class, 'show']);
    