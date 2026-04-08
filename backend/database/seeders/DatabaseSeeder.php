<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'full_name' => 'Demo User',
            'email' => 'demo@jobmind.test',
            'password' => bcrypt('password'), // password is 'password'
            'skills' => ['Vue 3', 'Laravel', 'PHP'],
            'experience' => '3 years of web development.',
            'preferences' => ['remote' => true, 'min_salary' => 80000]
        ]);

        $this->call([
            JobSeeder::class,
        ]);
    }
}
