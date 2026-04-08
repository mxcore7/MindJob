<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Senior Vue.js Developer',
                'company' => 'TechCorp',
                'description' => 'Looking for an experienced Vue 3 developer to build scalable frontends.',
                'skills_required' => json_encode(['Vue 3', 'JavaScript', 'Tailwind CSS']),
                'location' => 'Remote',
                'salary' => '$90k - $120k',
                'source' => 'Internal',
            ],
            [
                'title' => 'Laravel Backend Engineer',
                'company' => 'SaaSify',
                'description' => 'Develop robust API endpoints with Laravel 11 and PostgreSQL.',
                'skills_required' => json_encode(['Laravel', 'PHP', 'PostgreSQL', 'API']),
                'location' => 'New York, NY (Hybrid)',
                'salary' => '$100k - $130k',
                'source' => 'LinkedIn',
            ],
            [
                'title' => 'Full Stack Developer',
                'company' => 'StartupInc',
                'description' => 'Join our early stage startup. We use Vue on the frontend and Laravel on the backend.',
                'skills_required' => json_encode(['Vue.js', 'Laravel', 'Tailwind', 'MySQL']),
                'location' => 'San Francisco, CA',
                'salary' => '$120k - $150k',
                'source' => 'Indeed',
            ],
            [
                'title' => 'Frontend UI/UX Developer',
                'company' => 'DesignWorks',
                'description' => 'Create pixel-perfect SaaS dashboards using Vue 3 and Tailwind CSS.',
                'skills_required' => json_encode(['Vue 3', 'Tailwind CSS', 'Figma', 'UI/UX']),
                'location' => 'Remote',
                'salary' => '$80k - $110k',
                'source' => 'JobMind',
            ],
            [
                'title' => 'Backend Core Engineer',
                'company' => 'DataFlow',
                'description' => 'Build high-performance database queries and server applications.',
                'skills_required' => json_encode(['PHP', 'PostgreSQL', 'Redis', 'Docker']),
                'location' => 'London, UK',
                'salary' => '£70k - £90k',
                'source' => 'Internal',
            ],
        ];

        foreach ($jobs as $job) {
            \App\Models\Job::create($job);
        }
    }
}
