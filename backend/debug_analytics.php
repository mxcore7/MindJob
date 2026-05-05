<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DEBUG ANALYTICS ===" . PHP_EOL;
echo "Total jobs: " . Job::count() . PHP_EOL;

// Check columns
echo PHP_EOL . "=== COLUMNS ===" . PHP_EOL;
$columns = Schema::getColumnListing('jobs');
echo implode(', ', $columns) . PHP_EOL;

// Check location data
echo PHP_EOL . "=== LOCATION DATA ===" . PHP_EOL;
echo "Null location: " . Job::whereNull('location')->count() . PHP_EOL;
echo "Empty location: " . Job::where('location', '')->count() . PHP_EOL;

echo PHP_EOL . "=== TOP 10 LOCATIONS ===" . PHP_EOL;
$topCities = Job::select('location', DB::raw('count(*) as total'))
    ->whereNotNull('location')
    ->where('location', '!=', '')
    ->groupBy('location')
    ->orderBy('total', 'desc')
    ->limit(10)
    ->get();
foreach ($topCities as $city) {
    echo "  " . ($city->location ?: 'NULL') . " => " . $city->total . PHP_EOL;
}

// Check is_remote
echo PHP_EOL . "=== IS_REMOTE ===" . PHP_EOL;
if (Schema::hasColumn('jobs', 'is_remote')) {
    echo "is_remote column EXISTS" . PHP_EOL;
    echo "is_remote = true: " . Job::where('is_remote', true)->count() . PHP_EOL;
    echo "is_remote = false: " . Job::where('is_remote', false)->count() . PHP_EOL;
    echo "is_remote IS NULL: " . Job::whereNull('is_remote')->count() . PHP_EOL;
} else {
    echo "is_remote column DOES NOT EXIST!" . PHP_EOL;
}

// Check date ranges
echo PHP_EOL . "=== DATE RANGES ===" . PHP_EOL;
echo "Jobs in 2025: " . Job::whereBetween('created_at', ['2025-01-01', '2025-12-31'])->count() . PHP_EOL;
echo "Jobs in 2026 Q1: " . Job::whereBetween('created_at', ['2026-02-01', '2026-04-30'])->count() . PHP_EOL;
echo "Min created_at: " . Job::min('created_at') . PHP_EOL;
echo "Max created_at: " . Job::max('created_at') . PHP_EOL;

// Check skills_required column type
echo PHP_EOL . "=== SKILLS ===" . PHP_EOL;
$sample = Job::whereNotNull('skills_required')->first();
if ($sample) {
    echo "Sample skills_required type: " . gettype($sample->getRawOriginal('skills_required')) . PHP_EOL;
    echo "Sample skills_required value: " . substr($sample->getRawOriginal('skills_required'), 0, 200) . PHP_EOL;
} else {
    echo "No jobs with skills_required" . PHP_EOL;
}

// Test the actual API query
echo PHP_EOL . "=== SIMULATING locationAnalysis for 2025 ===" . PHP_EOL;
$topCities2025 = Job::whereBetween('created_at', ['2025-01-01', '2025-12-31'])
    ->select('location', DB::raw('count(*) as total'))
    ->groupBy('location')
    ->orderBy('total', 'desc')
    ->limit(10)
    ->get();
echo "Top cities result count: " . $topCities2025->count() . PHP_EOL;
foreach ($topCities2025 as $city) {
    echo "  " . ($city->location ?: 'NULL/EMPTY') . " => " . $city->total . PHP_EOL;
}

echo PHP_EOL . "=== SIMULATING remoteStats for 2025 ===" . PHP_EOL;
$remoteStats = Job::whereBetween('created_at', ['2025-01-01', '2025-12-31'])
    ->select(
        DB::raw('SUM(CASE WHEN is_remote = true THEN 1 ELSE 0 END) as remote'),
        DB::raw('SUM(CASE WHEN is_remote = false THEN 1 ELSE 0 END) as onsite')
    )
    ->first();
echo "Remote: " . ($remoteStats->remote ?? 'NULL') . PHP_EOL;
echo "Onsite: " . ($remoteStats->onsite ?? 'NULL') . PHP_EOL;
