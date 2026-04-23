<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    public function index()
    {
        $totalJobs = Job::count();

        $jobsBySource = Job::select('source', DB::raw('count(*) as count'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->get();

        $jobsByLocation = Job::select('location', DB::raw('count(*) as count'))
            ->whereNotNull('location')
            ->groupBy('location')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $jobs = Job::whereNotNull('skills_required')->get(['skills_required']);
        $skillsCount = [];
        foreach ($jobs as $job) {
            if (is_array($job->skills_required)) {
                foreach ($job->skills_required as $skill) {
                    if (is_string($skill) && trim($skill) !== '') {
                        $s = trim($skill);
                        // Standardize string a bit (optional, maybe title case)
                        $s = mb_convert_case($s, MB_CASE_TITLE, "UTF-8");
                        $skillsCount[$s] = ($skillsCount[$s] ?? 0) + 1;
                    }
                }
            }
        }
        arsort($skillsCount);
        $topSkillsArray = array_slice($skillsCount, 0, 5, true);
        
        $topSkills = [];
        foreach ($topSkillsArray as $name => $count) {
            $topSkills[] = [
                'name' => $name,
                'count' => $count
            ];
        }

        // Mocked or calculated metrics
        $averageMatchingRate = "84%";
        $duplicationRate = "1.2%";

        return response()->json([
            'total_jobs' => $totalJobs,
            'jobs_by_source' => $jobsBySource,
            'jobs_by_location' => $jobsByLocation,
            'top_skills' => $topSkills,
            'average_matching_rate' => $averageMatchingRate,
            'duplication_rate' => $duplicationRate,
        ]);
    }
}
