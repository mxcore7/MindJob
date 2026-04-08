<?php

namespace App\Console\Commands;

use App\Services\ExternalJobService;
use Illuminate\Console\Command;

class FetchExternalJobs extends Command
{
    protected $signature = 'jobs:fetch 
        {query=developer : Search query for jobs}
        {--location= : Location filter (e.g. "Paris", "New York")}
        {--pages=1 : Number of pages to fetch}';

    protected $description = 'Fetch real job offers from external APIs (JSearch/RapidAPI)';

    public function handle(ExternalJobService $service): int
    {
        $query = $this->argument('query');
        $location = $this->option('location') ?? '';
        $pages = (int) $this->option('pages');

        $this->info("🔍 Searching for: \"{$query}\"" . ($location ? " in {$location}" : ''));

        $jobs = $service->fetchJobs($query, $location, $pages);

        if (empty($jobs)) {
            $this->warn('No new jobs were imported. Either the API key is missing, or all jobs already exist in the database.');
            return Command::SUCCESS;
        }

        $this->info("✅ Imported " . count($jobs) . " new job(s)!");

        foreach ($jobs as $job) {
            $this->line("  • {$job->title} at {$job->company} ({$job->location})");
        }

        return Command::SUCCESS;
    }
}
