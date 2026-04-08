<?php

namespace App\Services;

use App\Models\User;
use App\Models\Job;

class RecommendationService
{
    /**
     * Get recommended jobs for a user and append a match_score
     */
    public function getRecommendedJobs(User $user, $limit = 10)
    {
        $userSkills = $user->skills ?? [];
        if (is_string($userSkills)) {
            $userSkills = json_decode($userSkills, true) ?? [];
        }
        
        // If user has no skills, return recent jobs without score
        if (empty($userSkills)) {
            return Job::latest()->take($limit)->get()->map(function ($job) {
                $job->match_score = 0;
                return $job;
            });
        }

        $userSkillsLower = array_map('strtolower', $userSkills);
        
        $jobs = Job::all();
        
        // Calculate match score for each job
        $jobs->each(function ($job) use ($userSkillsLower) {
            $jobSkills = $job->skills_required ?? [];
            if (is_string($jobSkills)) {
                $jobSkills = json_decode($jobSkills, true) ?? [];
            }
            if (empty($jobSkills)) {
                $job->match_score = 0;
                return;
            }
            
            $jobSkillsLower = array_map('strtolower', $jobSkills);
            
            // Calculate precision (intersection / job skills count)
            $intersection = array_intersect($userSkillsLower, $jobSkillsLower);
            
            $score = count($intersection) / count($jobSkillsLower) * 100;
            $job->match_score = round($score);
        });
        
        // Sort by match score descending, then by created_at
        return $jobs->sortByDesc('match_score')->take($limit)->values();
    }
}
