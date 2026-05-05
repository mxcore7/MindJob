<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Job;

// Sample some jobs with their descriptions to check for remote keywords
$jobs = Job::take(5)->get(['id', 'title', 'location', 'description', 'is_remote', 'source']);
foreach ($jobs as $job) {
    echo "ID: {$job->id}" . PHP_EOL;
    echo "Title: {$job->title}" . PHP_EOL;
    echo "Location: {$job->location}" . PHP_EOL;
    echo "Source: {$job->source}" . PHP_EOL;
    echo "is_remote: " . ($job->is_remote ? 'true' : 'false') . PHP_EOL;
    echo "Desc (100 chars): " . substr($job->description, 0, 100) . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Check how many have remote-related keywords in description or title
echo PHP_EOL . "=== REMOTE KEYWORD DETECTION ===" . PHP_EOL;
$remoteKeywords = ['télétravail', 'teletravail', 'remote', 'distance', 'domicile', 'hybride', 'hybrid'];
foreach ($remoteKeywords as $keyword) {
    $count = Job::where('description', 'LIKE', "%{$keyword}%")
        ->orWhere('title', 'LIKE', "%{$keyword}%")
        ->orWhere('location', 'LIKE', "%{$keyword}%")
        ->count();
    echo "'{$keyword}' found in: {$count} jobs" . PHP_EOL;
}

// Check unique sources
echo PHP_EOL . "=== SOURCES ===" . PHP_EOL;
$sources = Job::select('source', \Illuminate\Support\Facades\DB::raw('count(*) as c'))->groupBy('source')->get();
foreach ($sources as $s) {
    echo "{$s->source}: {$s->c}" . PHP_EOL;
}
