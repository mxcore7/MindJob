<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RecommendationService;

class RecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 10);
        
        $recommendedJobs = $this->recommendationService->getRecommendedJobs($user, $limit);
        
        return response()->json($recommendedJobs);
    }
}
