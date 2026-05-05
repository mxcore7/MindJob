<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Récupère la plage de dates selon la période
     */
    private function getDateRange($period)
    {
        if ($period == '2025') {
            return ['2025-01-01', '2025-12-31'];
        }
        // Période 01/02/2026 → 30/04/2026
        return ['2026-02-01', '2026-04-30'];
    }

    /**
     * Analyse géographique - Top villes
     * GET /api/analytics/locations
     */
    public function locationAnalysis(Request $request)
    {
        $period = $request->input('period', '2025');
        [$startDate, $endDate] = $this->getDateRange($period);
        
        // Top 10 villes
        $topCities = Job::whereBetween('created_at', [$startDate, $endDate])
            ->select('location', DB::raw('count(*) as total'))
            ->groupBy('location')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        // Répartition remote vs présentiel
        $remoteStats = Job::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(CASE WHEN is_remote = true THEN 1 ELSE 0 END) as remote'),
                DB::raw('SUM(CASE WHEN is_remote = false THEN 1 ELSE 0 END) as onsite')
            )
            ->first();
        
        // Offres par région
        $regions = [
            'Île-de-France' => ['Paris', 'Saint-Denis', 'Boulogne', 'Nanterre'],
            'Auvergne-Rhône-Alpes' => ['Lyon', 'Grenoble', 'Clermont-Ferrand'],
            'Provence-Alpes-Côte d\'Azur' => ['Marseille', 'Nice', 'Toulon'],
            'Occitanie' => ['Toulouse', 'Montpellier', 'Nîmes'],
            'Nouvelle-Aquitaine' => ['Bordeaux', 'Limoges', 'Poitiers'],
            'Hauts-de-France' => ['Lille', 'Amiens', 'Valenciennes'],
            'Grand Est' => ['Strasbourg', 'Nancy', 'Reims'],
            'Pays de la Loire' => ['Nantes', 'Angers', 'Le Mans'],
            'Bretagne' => ['Rennes', 'Brest', 'Vannes'],
            'Normandie' => ['Rouen', 'Caen', 'Le Havre']
        ];
        
        $regionStats = [];
        foreach ($regions as $region => $cities) {
            $regionStats[$region] = Job::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('location', $cities)
                ->count();
        }
        
        arsort($regionStats);
        $topRegions = array_slice($regionStats, 0, 5, true);
        
        return response()->json([
            'top_cities' => $topCities,
            'remote_vs_onsite' => $remoteStats,
            'top_regions' => $topRegions,
            'total_jobs' => Job::whereBetween('created_at', [$startDate, $endDate])->count(),
            'period' => $period,
            'date_range' => ['start' => $startDate, 'end' => $endDate]
        ]);
    }
    
    /**
     * Analyse des salaires
     * GET /api/analytics/salaries
     */
    public function salaryAnalysis(Request $request)
    {
        $period = $request->input('period', '2025');
        [$startDate, $endDate] = $this->getDateRange($period);
        
        // Salaire moyen par type de contrat
        $salaryByContract = Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('salary_min')
            ->select(
                'contract_type',
                DB::raw('AVG(salary_min) as avg_salary_min'),
                DB::raw('AVG(salary_max) as avg_salary_max'),
                DB::raw('MIN(salary_min) as min_salary'),
                DB::raw('MAX(salary_max) as max_salary'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('contract_type')
            ->orderBy('avg_salary_min', 'desc')
            ->get();
        
        // Salaire moyen par ville
        $salaryByCity = Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('salary_min')
            ->select(
                'location',
                DB::raw('AVG(salary_min) as avg_salary'),
                DB::raw('COUNT(*) as job_count')
            )
            ->groupBy('location')
            ->having('job_count', '>=', 5)
            ->orderBy('avg_salary', 'desc')
            ->limit(10)
            ->get();
        
        // Distribution des salaires
        $salaryRanges = [
            'Moins de 35k' => [0, 35000],
            '35k - 45k' => [35000, 45000],
            '45k - 55k' => [45000, 55000],
            '55k - 65k' => [55000, 65000],
            '65k - 80k' => [65000, 80000],
            '80k - 100k' => [80000, 100000],
            'Plus de 100k' => [100000, 1000000]
        ];
        
        $distribution = [];
        foreach ($salaryRanges as $label => $range) {
            $count = Job::whereBetween('created_at', [$startDate, $endDate])
                ->whereBetween('salary_min', [$range[0], $range[1]])
                ->count();
            $distribution[$label] = $count;
        }
        
        // Statistiques globales
        $globalStats = Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('salary_min')
            ->select(
                DB::raw('AVG(salary_min) as overall_avg_min'),
                DB::raw('AVG(salary_max) as overall_avg_max'),
                DB::raw('MIN(salary_min) as global_min'),
                DB::raw('MAX(salary_max) as global_max')
            )
            ->first();
        
        return response()->json([
            'by_contract' => $salaryByContract,
            'by_city' => $salaryByCity,
            'distribution' => $distribution,
            'global_stats' => $globalStats,
            'period' => $period,
            'date_range' => ['start' => $startDate, 'end' => $endDate]
        ]);
    }
    
    /**
     * Analyse des compétences
     * GET /api/analytics/skills
     */
    public function skillsAnalysis(Request $request)
    {
        $period = $request->input('period', '2025');
        $limit = $request->input('limit', 20);
        [$startDate, $endDate] = $this->getDateRange($period);
        
        $jobs = Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('skills_required')
            ->pluck('skills_required');
        
        $skillsCount = [];
        
        foreach ($jobs as $skillsString) {
            if (empty($skillsString)) continue;
            
            $skills = preg_split('/[,|]/', $skillsString);
            foreach ($skills as $skill) {
                $skill = trim($skill);
                if (!empty($skill) && strlen($skill) > 1) {
                    $skillsCount[$skill] = ($skillsCount[$skill] ?? 0) + 1;
                }
            }
        }
        
        arsort($skillsCount);
        $topSkills = array_slice($skillsCount, 0, $limit, true);
        
        $categories = [
            'Backend' => ['PHP', 'Laravel', 'Symfony', 'Python', 'Django', 'Java', 'Spring', 'Node.js'],
            'Frontend' => ['React', 'Vue.js', 'Angular', 'JavaScript', 'TypeScript', 'HTML', 'CSS'],
            'Base de données' => ['SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'Redis'],
            'DevOps' => ['Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'CI/CD', 'Git'],
            'Data' => ['Python', 'Pandas', 'Machine Learning', 'TensorFlow', 'Spark', 'Hadoop', 'ETL']
        ];
        
        $categoryStats = [];
        foreach ($categories as $category => $skillsList) {
            $total = 0;
            foreach ($skillsList as $skill) {
                $total += $skillsCount[$skill] ?? 0;
            }
            $categoryStats[$category] = $total;
        }
        
        return response()->json([
            'top_skills' => $topSkills,
            'category_distribution' => $categoryStats,
            'total_jobs_analyzed' => $jobs->count(),
            'unique_skills_count' => count($skillsCount),
            'period' => $period,
            'date_range' => ['start' => $startDate, 'end' => $endDate]
        ]);
    }
    
    /**
 * Dashboard complet
 * GET /api/analytics/dashboard
 */
public function dashboard(Request $request)
{
    $period = $request->input('period', '2025');
    [$startDate, $endDate] = $this->getDateRange($period);
    
    $locations = $this->locationAnalysis($request);
    $salaries = $this->salaryAnalysis($request);
    $skills = $this->skillsAnalysis($request);
    
    return response()->json([
        'locations' => $locations->getData(true),
        'salaries' => $salaries->getData(true),
        'skills' => $skills->getData(true),
        'summary' => [
            'total_jobs' => Job::whereBetween('created_at', [$startDate, $endDate])->count(),
            'avg_salary' => round(Job::whereBetween('created_at', [$startDate, $endDate])->avg('salary_min') ?? 0),
            'remote_rate' => round(
                Job::whereBetween('created_at', [$startDate, $endDate])->where('is_remote', true)->count() / 
                max(Job::whereBetween('created_at', [$startDate, $endDate])->count(), 1) * 100, 2
            )
        ]
    ]);
}