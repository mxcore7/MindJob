<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalJobService
{
    protected string $rapidApiKey;
    protected string $rapidApiHost = 'jsearch.p.rapidapi.com';

    public function __construct()
    {
        $this->rapidApiKey = config('services.rapidapi.key', '');
    }

    /**
     * Search and fetch jobs from JSearch (RapidAPI)
     * Aggregates results from Google Jobs, Indeed, LinkedIn, etc.
     */
    public function fetchJobs(string $query = 'developer', string $location = '', int $numPages = 1): array
    {
        if (empty($this->rapidApiKey)) {
            Log::warning('ExternalJobService: RAPIDAPI_KEY is not set in .env');
            return [];
        }

        $searchQuery = $location ? "{$query} in {$location}" : $query;

        try {
            $response = Http::withHeaders([
                'x-rapidapi-key' => $this->rapidApiKey,
                'x-rapidapi-host' => $this->rapidApiHost,
            ])->get("https://{$this->rapidApiHost}/search", [
                'query' => $searchQuery,
                'page' => '1',
                'num_pages' => (string) $numPages,
                'date_posted' => 'month', // jobs from the last month
            ]);

            if ($response->successful()) {
                $data = $response->json('data', []);
                return $this->normalizeAndStore($data);
            }

            Log::error('ExternalJobService: API error', [
                'status' => $response->status(),
                'body' => $response->body(),
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

            // Extract skills from description (basic keyword extraction)
            $skills = $this->extractSkills($extJob['job_description'] ?? '');

            // Build salary string
            $salary = $this->buildSalaryString($extJob);

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
            ]);

            $stored[] = $job;
        }

        return $stored;
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

        return array_slice(array_unique($found), 0, 8); // max 8 skills
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
