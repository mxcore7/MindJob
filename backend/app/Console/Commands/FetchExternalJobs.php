<?php

namespace App\Console\Commands;

use App\Services\ExternalJobService;
use Illuminate\Console\Command;

class FetchExternalJobs extends Command
{
    protected $signature = 'jobs:fetch 
        {query=informatique : Search query for jobs}
        {--location= : Location filter (e.g. "Paris", "75" for France Travail)}
        {--pages=1 : Number of pages to fetch (JSearch only)}
        {--country=us : Country code (us, fr, gb, de, etc.)}
        {--source=all : Source to fetch from: all, jsearch, francetravail}';

    protected $description = 'Fetch real job offers from external APIs (JSearch + France Travail)';

    public function handle(ExternalJobService $service): int
    {
        $query = $this->argument('query');
        $location = $this->option('location') ?? '';
        $pages = (int) $this->option('pages');
        $country = $this->option('country');
        $source = strtolower($this->option('source'));

        $this->info("🔍 Searching for: \"{$query}\"" . ($location ? " in {$location}" : '') . " (source: {$source})");

        $jobs = [];

        if ($source === 'all' || $source === 'jsearch') {
            $this->info('📡 Fetching from JSearch...');
            $jsearchJobs = $service->fetchJobs($query, $location, $pages, $country);
            $this->info("   → " . count($jsearchJobs) . " new job(s) from JSearch");
            $jobs = array_merge($jobs, $jsearchJobs);
        }

        if ($source === 'all' || $source === 'francetravail') {
            $this->info('🇫🇷 Fetching from France Travail...');
            $ftJobs = $service->fetchFranceTravailJobs($query, $location);
            $this->info("   → " . count($ftJobs) . " new job(s) from France Travail");
            $jobs = array_merge($jobs, $ftJobs);
        }

        if (empty($jobs)) {
            $this->warn('No new jobs were imported. Check your API keys or try different search terms.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info("✅ Total imported: " . count($jobs) . " new job(s)!");

        foreach ($jobs as $job) {
            $contractBadge = $job->contract_type ? " [{$job->contract_type}]" : '';
            $this->line("  • {$job->title} at {$job->company} ({$job->location}){$contractBadge} — {$job->source}");
        }

        return Command::SUCCESS;
    }
}
