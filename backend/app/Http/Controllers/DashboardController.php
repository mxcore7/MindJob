<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;
use App\Services\RecommendationService;

class DashboardController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        $totalJobs = Job::count();
        $recommendedJobs = $this->recommendationService->getRecommendedJobs($user, 100); // get top 100 to count matches
        
        $highMatchJobs = $recommendedJobs->filter(function($job) {
            return $job->match_score >= 80;
        })->count();

        $mediumMatchJobs = $recommendedJobs->filter(function($job) {
            return $job->match_score >= 50 && $job->match_score < 80;
        })->count();

        return response()->json([
            'total_jobs_available' => $totalJobs,
            'high_match_jobs' => $highMatchJobs,
            'medium_match_jobs' => $mediumMatchJobs,
            'user_skills_count' => count($user->skills ?? []),
            'recent_recommendations' => $recommendedJobs->take(5)
        ]);
    }
}
