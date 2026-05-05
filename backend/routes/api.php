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
use App\Http\Controllers\AnalyticsController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes Analytics (publiques pour le rapport)
Route::get('/analytics/locations', [AnalyticsController::class, 'locationAnalysis']);
Route::get('/analytics/salaries', [AnalyticsController::class, 'salaryAnalysis']);
Route::get('/analytics/skills', [AnalyticsController::class, 'skillsAnalysis']);
Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);

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
    Route::get('/recommendations/refresh', [RecommendationController::class, 'refresh']);
    
    // Upload de fichiers
    Route::post('/upload/cv', [\App\Http\Controllers\UploadController::class, 'uploadResume']);
    Route::post('/upload/cover-letter', [\App\Http\Controllers\UploadController::class, 'uploadCoverLetter']);
    Route::post('/upload/application/{applicationId}', [\App\Http\Controllers\UploadController::class, 'uploadApplicationFiles']);
    Route::delete('/upload/cv', [\App\Http\Controllers\UploadController::class, 'deleteResume']);
    Route::get('/download/cv/{userId}', [\App\Http\Controllers\UploadController::class, 'downloadCv']);
});