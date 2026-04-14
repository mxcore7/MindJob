<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalJobService
{
    protected string $rapidApiKey;
    protected string $rapidApiHost = 'jsearch.p.rapidapi.com';
    protected FranceTravailService $franceTravailService;

    public function __construct(FranceTravailService $franceTravailService)
    {
        $this->rapidApiKey = config('services.rapidapi.key', '');
        $this->franceTravailService = $franceTravailService;
    }

    /**
     * Fetch jobs from ALL sources (JSearch + France Travail)
     */
    public function fetchAllSources(string $query = 'developer', string $location = '', int $numPages = 1, string $country = 'us'): array
    {
        $allJobs = [];

        // JSearch
        $jsearchJobs = $this->fetchJobs($query, $location, $numPages, $country);
        $allJobs = array_merge($allJobs, $jsearchJobs);

        // France Travail
        $ftJobs = $this->fetchFranceTravailJobs($query, $location);
        $allJobs = array_merge($allJobs, $ftJobs);

        return $allJobs;
    }

    /**
     * Fetch jobs from France Travail API
     */
    public function fetchFranceTravailJobs(string $query = 'informatique', string $location = ''): array
    {
        return $this->franceTravailService->fetchAndStore($query, $location);
    }

    /**
     * Search and fetch jobs from JSearch (RapidAPI)
     * Aggregates results from Google Jobs, Indeed, LinkedIn, etc.
     */
    public function fetchJobs(string $query = 'developer', string $location = '', int $numPages = 1, string $country = 'us'): array
    {
        if (empty($this->rapidApiKey)) {
            Log::warning('ExternalJobService: RAPIDAPI_KEY is not set in .env');
            return [];
        }

        // Format: "X jobs in Y" as per JSearch API docs
        $searchQuery = $location ? "{$query} jobs in {$location}" : "{$query} jobs";

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-rapidapi-key' => $this->rapidApiKey,
                    'x-rapidapi-host' => $this->rapidApiHost,
                ])->get("https://{$this->rapidApiHost}/search", [
                    'query' => $searchQuery,
                    'page' => '1',
                    'num_pages' => (string) $numPages,
                    'country' => $country,
                    'date_posted' => 'all',
                ]);

            if ($response->successful()) {
                $data = $response->json('data', []);
                Log::info("ExternalJobService: API returned " . count($data) . " jobs for '{$searchQuery}'");
                return $this->normalizeAndStore($data);
            }

            Log::error('ExternalJobService: API error', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
            ]);
        } catch (\Exception $e) {
            Log::error('ExternalJobService: Exception', ['message' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Normalize JSearch API data and store in the jobs table
     */
    protected function normalizeAndStore(array $externalJobs): array
    {
        $stored = [];

        foreach ($externalJobs as $extJob) {
            $title = $extJob['job_title'] ?? 'Unknown Position';
            $company = $extJob['employer_name'] ?? 'Unknown Company';
            $source = $extJob['job_publisher'] ?? 'JSearch';

            // Skip if job with same title+company already exists
            $exists = Job::where('title', $title)
                ->where('company', $company)
                ->exists();

            if ($exists) {
                continue;
            }

            // Extract skills from description
            $skills = $this->extractSkills($extJob['job_description'] ?? '');

            // Build salary string
            $salary = $this->buildSalaryString($extJob);

            // Map employment type to contract_type
            $contractType = $this->mapJSearchEmploymentType($extJob['job_employment_type'] ?? null);

            $job = Job::create([
                'title' => $title,
                'company' => $company,
                'description' => $this->cleanDescription($extJob['job_description'] ?? ''),
                'skills_required' => $skills,
                'location' => $extJob['job_city']
                    ? ($extJob['job_city'] . ', ' . ($extJob['job_country'] ?? ''))
                    : ($extJob['job_country'] ?? 'Remote'),
                'salary' => $salary,
                'source' => $source,
                'contract_type' => $contractType,
            ]);

            $stored[] = $job;
        }

        return $stored;
    }

    /**
     * Map JSearch employment type to a readable contract type
     */
    protected function mapJSearchEmploymentType(?string $type): ?string
    {
        if (!$type) return null;

        $map = [
            'FULLTIME'   => 'Full-time',
            'PARTTIME'   => 'Part-time',
            'CONTRACTOR' => 'Contractor',
            'INTERN'     => 'Internship',
        ];

        return $map[strtoupper($type)] ?? $type;
    }

    /**
     * Extract probable skills from job description
     */
    protected function extractSkills(string $description): array
    {
        $knownSkills = [
            'PHP', 'Laravel', 'Vue', 'Vue.js', 'React', 'Angular', 'Node.js',
            'JavaScript', 'TypeScript', 'Python', 'Django', 'Flask',
            'Java', 'Spring', 'C#', '.NET', 'Go', 'Rust', 'Ruby',
            'SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'Redis',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP',
            'Git', 'CI/CD', 'REST', 'GraphQL', 'API',
            'HTML', 'CSS', 'Tailwind', 'Bootstrap', 'Sass',
            'Linux', 'Nginx', 'Apache',
            'Figma', 'Jira', 'Agile', 'Scrum',
            'Machine Learning', 'AI', 'Data Science',
            'Flutter', 'Swift', 'Kotlin', 'React Native',
        ];

        $found = [];
        $descriptionLower = strtolower($description);

        foreach ($knownSkills as $skill) {
            if (stripos($descriptionLower, strtolower($skill)) !== false) {
                $found[] = $skill;
            }
        }

        return array_slice(array_unique($found), 0, 8);
    }

    /**
     * Build a salary string from JSearch data
     */
    protected function buildSalaryString(array $job): ?string
    {
        $min = $job['job_min_salary'] ?? null;
        $max = $job['job_max_salary'] ?? null;
        $period = $job['job_salary_period'] ?? 'year';

        if ($min && $max) {
            return '$' . number_format($min) . ' - $' . number_format($max) . '/' . strtolower($period);
        }
        if ($min) {
            return 'From $' . number_format($min) . '/' . strtolower($period);
        }
        if ($max) {
            return 'Up to $' . number_format($max) . '/' . strtolower($period);
        }

        return null;
    }

    /**
     * Clean HTML from description and truncate
     */
    protected function cleanDescription(string $description): string
    {
        $clean = strip_tags($description);
        $clean = preg_replace('/\s+/', ' ', $clean);
        return mb_substr(trim($clean), 0, 2000);
    }
}
