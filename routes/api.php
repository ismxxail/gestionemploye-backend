<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\EmployeurController;
use App\Http\Controllers\PresenceController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('/employeurs', EmployeurController::class);
    Route::get('/conges', [CongeController::class, 'index']);
    Route::put('/conges/{conge}', [CongeController::class, 'updateStatus']);
    Route::get('/presences', [PresenceController::class, 'index']);
});
Route::middleware(['auth:sanctum', 'role:employeur'])->group(function () {
    Route::get('/workhours/today', [PresenceController::class, 'getTodayWorkHours']);
    Route::post('/conge', [CongeController::class, 'DemandeConge']);
    Route::get('/conge/{employeurId}', [CongeController::class, 'indexByEmployeur']);
    Route::get('/presence/{employeurId}', [PresenceController::class, 'getByEmployeur']);
    Route::post('/presence/checkin/{employeurId}', [PresenceController::class, 'CheckIn']);
    Route::post('/presence/checkout/{employeurId}', [PresenceController::class, 'CheckOut']);
    Route::get('/workdays', [PresenceController::class, 'getWorkDays']);
});