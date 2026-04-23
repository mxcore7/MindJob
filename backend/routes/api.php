<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\OffreController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/user', [ProfileController::class, 'update']);
    
    Route::apiResource('jobs', JobController::class);
    
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Job Applications
    Route::get('/applications', [JobApplicationController::class, 'index']);
    Route::post('/applications', [JobApplicationController::class, 'store']);
    Route::delete('/applications/{id}', [JobApplicationController::class, 'destroy']);

    // France Travail live search
    Route::get('/offres', [OffreController::class, 'index']);
    Route::get('/offres/{id}', [OffreController::class, 'show']);

    // KPI Dashboard
    Route::get('/kpis', [\App\Http\Controllers\KpiController::class, 'index']);
});
