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
        if ($period == '2026_q1') {
            return ['2026-02-01', '2026-04-30'];
        }
        // Fallback : toutes les données disponibles
        $min = Job::min('created_at') ?? now()->subYear()->toDateString();
        $max = Job::max('created_at') ?? now()->toDateString();
        return [substr($min, 0, 10), substr($max, 0, 10)];
    }

    /**
     * Analyse géographique - Top villes
     * GET /api/analytics/locations
     */
    public function locationAnalysis(Request $request)
    {
        $period = $request->input('period', 'all');
        [$startDate, $endDate] = $this->getDateRange($period);

        // Top 10 villes — normaliser en regroupant les variantes
        $topCitiesRaw = Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location', DB::raw('count(*) as total'))
            ->groupBy('location')
            ->orderBy('total', 'desc')
            ->get();

        // Normaliser les noms de villes (ex: "75 - Paris", "75 - PARIS", "75 - Paris 1er" -> "Paris")
        $cityMap = [];
        foreach ($topCitiesRaw as $row) {
            $normalized = $this->normalizeCity($row->location);
            $cityMap[$normalized] = ($cityMap[$normalized] ?? 0) + $row->total;
        }
        arsort($cityMap);
        $topCities = collect(array_slice($cityMap, 0, 10, true))
            ->map(fn($total, $city) => ['location' => $city, 'total' => $total])
            ->values();

        // Répartition remote vs présentiel — détection intelligente par mots-clés
        $remoteStats = $this->computeRemoteStats($startDate, $endDate);

        // Offres par région — recherche partielle (LIKE) pour capturer les variantes avec préfixe département
        $regions = [
            'Île-de-France' => ['Paris', 'Saint-Denis', 'Boulogne', 'Nanterre', 'Levallois', 'Courbevoie', 'Issy'],
            'Auvergne-Rhône-Alpes' => ['Lyon', 'Grenoble', 'Clermont-Ferrand', 'Saint-Étienne'],
            'Provence-Alpes-Côte d\'Azur' => ['Marseille', 'Nice', 'Toulon', 'Aix-en-Provence', 'Sophia Antipolis'],
            'Occitanie' => ['Toulouse', 'Montpellier', 'Nîmes', 'Perpignan'],
            'Nouvelle-Aquitaine' => ['Bordeaux', 'Limoges', 'Poitiers', 'La Rochelle'],
            'Hauts-de-France' => ['Lille', 'Amiens', 'Valenciennes', 'Roubaix'],
            'Grand Est' => ['Strasbourg', 'Nancy', 'Reims', 'Metz'],
            'Pays de la Loire' => ['Nantes', 'Angers', 'Le Mans'],
            'Bretagne' => ['Rennes', 'Brest', 'Vannes'],
            'Normandie' => ['Rouen', 'Caen', 'Le Havre']
        ];

        $regionStats = [];
        foreach ($regions as $region => $cities) {
            $query = Job::whereBetween('created_at', [$startDate, $endDate]);
            $query->where(function ($q) use ($cities) {
                foreach ($cities as $city) {
                    $q->orWhere('location', 'LIKE', "%{$city}%");
                }
            });
            $regionStats[$region] = $query->count();
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
     * Normalise un nom de ville (supprime préfixes département, arrondissements, etc.)
     */
    private function normalizeCity($location)
    {
        if (empty($location)) return 'Non spécifié';

        // Supprimer le préfixe département (ex: "75 - ")
        $city = preg_replace('/^\d{2,3}\s*-\s*/', '', $location);

        // Supprimer les suffixes d'arrondissement (ex: "Paris 1er Arrondissement", "PARIS 09")
        $city = preg_replace('/\s+\d+(er|e|ème)?\s*(Arrondissement)?$/i', '', $city);
        $city = preg_replace('/\s+\d{2}$/i', '', $city);

        // Supprimer les suffixes comme "(Dept.)"
        $city = preg_replace('/\s*\(.*\)\s*$/', '', $city);

        // Normaliser la casse
        $city = mb_convert_case(trim($city), MB_CASE_TITLE, 'UTF-8');

        return $city;
    }

    /**
     * Calcule les statistiques remote/onsite en se basant sur les mots-clés
     * dans location, description et le champ is_remote
     */
    private function computeRemoteStats($startDate, $endDate)
    {
        $jobs = Job::whereBetween('created_at', [$startDate, $endDate])->get(['id', 'location', 'description', 'is_remote']);

        $remote = 0;
        $onsite = 0;

        $remoteKeywords = ['télétravail', 'teletravail', 'remote', 'à distance', 'domicile', 'hybride', 'hybrid', 'home office', 'work from home'];

        foreach ($jobs as $job) {
            $isRemote = false;

            // 1. Vérifier le champ is_remote
            if ($job->is_remote) {
                $isRemote = true;
            }

            // 2. Vérifier les mots-clés dans location
            if (!$isRemote && $job->location) {
                $locationLower = mb_strtolower($job->location);
                foreach ($remoteKeywords as $keyword) {
                    if (str_contains($locationLower, $keyword)) {
                        $isRemote = true;
                        break;
                    }
                }
            }

            // 3. Vérifier les mots-clés dans description
            if (!$isRemote && $job->description) {
                $descLower = mb_strtolower($job->description);
                foreach ($remoteKeywords as $keyword) {
                    if (str_contains($descLower, $keyword)) {
                        $isRemote = true;
                        break;
                    }
                }
            }

            if ($isRemote) {
                $remote++;
            } else {
                $onsite++;
            }
        }

        return ['remote' => $remote, 'onsite' => $onsite];
    }

    /**
     * Analyse des salaires
     * GET /api/analytics/salaries
     */
    public function salaryAnalysis(Request $request)
    {
        $period = $request->input('period', 'all');
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
        $period = $request->input('period', 'all');
        $limit = $request->input('limit', 20);
        [$startDate, $endDate] = $this->getDateRange($period);

        $jobs = Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('skills_required')
            ->where('skills_required', '!=', '')
            ->pluck('skills_required');

        $skillsCount = [];

        foreach ($jobs as $skillsString) {
            if (empty($skillsString))
                continue;

            // Gérer le JSON double-encodé et les formats variés
            $skills = $this->parseSkills($skillsString);
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
            'Frontend' => ['React', 'Vue.js', 'Vue 3', 'Angular', 'JavaScript', 'TypeScript', 'HTML', 'CSS', 'Tailwind CSS'],
            'Base de données' => ['SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'Redis', 'BigQuery', 'Redshift'],
            'DevOps' => ['Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'CI/CD', 'Git', 'Terraform'],
            'Data' => ['Python', 'Pandas', 'Machine Learning', 'TensorFlow', 'Spark', 'Hadoop', 'ETL', 'dbt', 'Airflow', 'PyTorch', 'Scikit-learn', 'NumPy', 'PowerBI', 'Tableau']
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
     * Parse les compétences depuis différents formats (JSON, CSV, double-encodé)
     */
    private function parseSkills($skillsString)
    {
        // Tenter le décodage JSON
        $decoded = json_decode($skillsString, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Si c'est un JSON double-encodé (chaîne JSON contenant du JSON)
        if (is_string($decoded)) {
            $innerDecoded = json_decode($decoded, true);
            if (is_array($innerDecoded)) {
                return $innerDecoded;
            }
        }

        // Fallback : split par virgule ou pipe
        return preg_split('/[,|]/', $skillsString);
    }

    /**
     * Dashboard complet
     * GET /api/analytics/dashboard
     */
    public function dashboard(Request $request)
    {
        $period = $request->input('period', 'all');
        [$startDate, $endDate] = $this->getDateRange($period);

        $locations = $this->locationAnalysis($request);
        $salaries = $this->salaryAnalysis($request);
        $skills = $this->skillsAnalysis($request);

        // Calcul du taux remote via la même logique intelligente
        $remoteStats = $this->computeRemoteStats($startDate, $endDate);
        $totalJobs = Job::whereBetween('created_at', [$startDate, $endDate])->count();
        $remoteRate = $totalJobs > 0 ? round(($remoteStats['remote'] / $totalJobs) * 100, 2) : 0;

        return response()->json([
            'locations' => $locations->getData(true),
            'salaries' => $salaries->getData(true),
            'skills' => $skills->getData(true),
            'summary' => [
                'total_jobs' => $totalJobs,
                'avg_salary' => round(Job::whereBetween('created_at', [$startDate, $endDate])->avg('salary_min') ?? 0),
                'remote_rate' => $remoteRate
            ]
        ]);
    }
}