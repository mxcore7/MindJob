<?php

namespace App\Console\Commands;

use App\Services\ExternalJobService;
use Illuminate\Console\Command;

class FetchExternalJobs extends Command
{
    protected $signature = 'jobs:fetch 
        {query=developer : Search query for jobs}
        {--location= : Location filter (e.g. "Chicago", "Paris")}
        {--pages=1 : Number of pages to fetch}
        {--country=us : Country code (us, fr, gb, de, etc.)}';

    protected $description = 'Fetch real job offers from external APIs (JSearch/RapidAPI)';

    public function handle(ExternalJobService $service): int
    {
        $query = $this->argument('query');
        $location = $this->option('location') ?? '';
        $pages = (int) $this->option('pages');
        $country = $this->option('country');

        $this->info("🔍 Searching for: \"{$query}\"" . ($location ? " in {$location}" : '') . " (country: {$country})");

        $jobs = $service->fetchJobs($query, $location, $pages, $country);

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
