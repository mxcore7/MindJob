<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class ImportMassiveJobs2025 extends Command
{
    protected $signature = 'jobs:import-massive-2025 
                            {--count=10000 : Nombre d\'offres à générer}
                            {--chunk=500 : Taille des lots}';

    protected $description = 'Génère un volume massif d\'offres pour l\'analyse 2025';

    private $titles = [
        'Data Scientist', 'Data Engineer', 'Data Analyst',
        'AI Engineer', 'Machine Learning Engineer', 'Data Architect',
        'BI Developer', 'Data Product Manager', 'Analytics Engineer',
        'Cloud Data Engineer', 'Data Consultant', 'Big Data Engineer',
        'Data Governance Manager', 'DataOps Engineer', 'Data Visualization Specialist'
    ];

    private $companies = [
        'Capgemini', 'Accenture', 'BNP Paribas', 'Société Générale', 'EDF',
        'Orange', 'TotalEnergies', 'LVMH', 'Airbus', 'Thales',
        'Dassault Systèmes', 'Sopra Steria', 'Atos', 'Ubisoft', 'Decathlon'
    ];

    private $locations = [
        'Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nantes',
        'Lille', 'Bordeaux', 'Strasbourg', 'Rennes', 'Nice',
        'Montpellier', 'Grenoble', 'Nancy', 'Tours', 'Orléans'
    ];

    private $contracts = ['CDI', 'CDD', 'Freelance', 'Stage', 'Alternance'];

    private $sources = ['France Travail', 'Indeed', 'LinkedIn', 'APEC', 'WelcomeKit'];

    private $skillsSets = [
        'Python,SQL,Pandas,NumPy',
        'SQL,Tableau,PowerBI,Excel',
        'Spark,Hadoop,AWS,EMR',
        'TensorFlow,PyTorch,Scikit-learn,MLflow',
        'Azure,Databricks,Python,ADF',
        'GCP,BigQuery,Looker,dbt',
        'Docker,Kubernetes,Terraform,CI/CD',
        'Airflow,dbt,Redshift,PostgreSQL',
        'Java,Spring,PostgreSQL,Kafka',
        'R,Shiny,ggplot2,Statistics'
    ];

    public function handle()
    {
        $count = (int) $this->option('count');
        $chunk = (int) $this->option('chunk');

        $this->info('═══════════════════════════════════════════════');
        $this->info('🚀 IMPORT MASSIF - DONNÉES 2025');
        $this->info('═══════════════════════════════════════════════');
        $this->info("📊 Offres à générer : {$count}");
        $this->info("📦 Taille des lots : {$chunk}");
        $this->newLine();

        // Vérifier l'espace disque
        $freeSpace = disk_free_space('C:') / 1024 / 1024 / 1024;
        $this->info("💾 Espace disque disponible : " . round($freeSpace, 2) . " GB");
        
        if ($freeSpace < 10) {
            $this->warn("⚠️ Espace disque faible !");
        }

        $bar = $this->output->createProgressBar(ceil($count / $chunk));
        $bar->start();

        $totalInserted = 0;

        for ($i = 0; $i < $count; $i += $chunk) {
            $jobs = [];
            $currentChunk = min($chunk, $count - $i);

            for ($j = 0; $j < $currentChunk; $j++) {
                $jobs[] = $this->generateJob($i + $j);
            }

            Job::insert($jobs);
            $totalInserted += $currentChunk;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ IMPORT TERMINÉ !");
        $this->info("📈 Total inséré : {$totalInserted} offres");
        
        // Statistiques finales
        $this->newLine();
        $this->table(
            ['Source', 'Nombre'],
            [
                ['France Travail', Job::where('source', 'France Travail')->whereYear('created_at', 2025)->count()],
                ['Indeed', Job::where('source', 'Indeed')->whereYear('created_at', 2025)->count()],
                ['LinkedIn', Job::where('source', 'LinkedIn')->whereYear('created_at', 2025)->count()],
                ['Autres', Job::whereYear('created_at', 2025)->whereNotIn('source', ['France Travail', 'Indeed', 'LinkedIn'])->count()],
                ['---', '---'],
                ['TOTAL 2025', Job::whereYear('created_at', 2025)->count()],
            ]
        );
    }

    private function generateJob($index)
    {
        $date = $this->randomDate('2025-01-01', '2025-12-31');
        $salaryMin = rand(35000, 65000);
        $salaryMax = rand(65000, 130000);
        
        return [
            'title' => $this->titles[array_rand($this->titles)],
            'company' => $this->companies[array_rand($this->companies)],
            'description' => $this->generateDescription(),
            'location' => $this->locations[array_rand($this->locations)],
            'skills_required' => $this->skillsSets[array_rand($this->skillsSets)],
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'salary_currency' => 'EUR',
            'contract_type' => $this->contracts[array_rand($this->contracts)],
            'source' => $this->sources[array_rand($this->sources)],
            'is_remote' => rand(0, 1),
            'views_count' => rand(0, 500),
            'created_at' => $date,
            'posted_at' => $date,
            'updated_at' => $date,
        ];
    }

    private function generateDescription()
    {
        $descriptions = [
            "Nous recherchons un(e) Data Scientist passionné(e) pour rejoindre notre équipe data. Missions : analyse de données, modélisation prédictive, mise en production de modèles.",
            "CDI - Poste basé sur Paris. Vous travaillerez sur des problématiques big data et contribuerez à la transformation data de l'entreprise.",
            "Rejoignez une équipe dynamique ! Compétences requises : Python, SQL, Machine Learning. Télétravail possible 2 jours/semaine.",
            "Poste ouvert aux juniors comme aux confirmés. Formation interne assurée. Environnement startup avec moyens d'entreprise.",
            "Vous serez responsable de la conception et du déploiement de pipelines de données scalables sur le cloud Azure/GCP."
        ];
        return $descriptions[array_rand($descriptions)];
    }

    private function randomDate($start, $end)
    {
        $timestamp = rand(strtotime($start), strtotime($end));
        return date('Y-m-d H:i:s', $timestamp);
    }
}
