<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FranceTravailService
{
    private string $clientId;
    private string $clientSecret;
    private string $tokenUrl = 'https://entreprise.francetravail.fr/connexion/oauth2/access_token';
    private string $apiUrl   = 'https://api.francetravail.io/partenaire/offresdemploi/v2';

    public function __construct()
    {
        $this->clientId     = config('services.france_travail.client_id', '');
        $this->clientSecret = config('services.france_travail.client_secret', '');
    }

    /**
     * Obtenir un access token OAuth2 (mis en cache 20 min)
     */
    public function getAccessToken(): string
    {
        return Cache::remember('france_travail_token', 1200, function () {
            $response = Http::withoutVerifying()->asForm()->post($this->tokenUrl . '?realm=/partenaire', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => 'api_offresdemploiv2 o2dsoffre',
            ]);

            if ($response->failed()) {
                Log::error('FranceTravail: Token request failed', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 500),
                ]);
                throw new \Exception('Impossible d\'obtenir le token France Travail: ' . $response->status());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Rechercher des offres d'emploi (résultats bruts de l'API)
     */
    public function searchOffres(array $params = []): array
    {
        $token = $this->getAccessToken();

        $response = Http::withoutVerifying()->withToken($token)
            ->withHeaders(['Accept' => 'application/json'])
            ->get("{$this->apiUrl}/offres/search", $params);

        if ($response->failed()) {
            Log::error('FranceTravail: Search failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new \Exception('Erreur API France Travail: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Récupérer le détail d'une offre
     */
    public function getOffre(string $id): array
    {
        $token = $this->getAccessToken();

        $response = Http::withoutVerifying()->withToken($token)
            ->withHeaders(['Accept' => 'application/json'])
            ->get("{$this->apiUrl}/offres/{$id}");

        if ($response->failed()) {
            throw new \Exception("Offre {$id} introuvable");
        }

        return $response->json() ?? [];
    }

    /**
     * Rechercher et stocker les offres dans la table jobs
     */
    public function fetchAndStore(string $query = 'informatique', string $location = '', int $maxResults = 149): array
    {
        if (empty($this->clientId) || $this->clientId === 'ton_client_id') {
            Log::warning('FranceTravailService: Client ID is not configured in .env');
            return [];
        }

        $params = array_filter([
            'motsCles'    => $query,
            'departement' => $location ?: null,
            'range'       => "0-{$maxResults}",
        ]);

        try {
            $data = $this->searchOffres($params);
            $resultats = $data['resultats'] ?? [];

            if (empty($resultats)) {
                Log::info('FranceTravailService: No results for query "' . $query . '"');
                return [];
            }

            Log::info("FranceTravailService: API returned " . count($resultats) . " offres for '{$query}'");
            return $this->normalizeAndStore($resultats);
        } catch (\Exception $e) {
            Log::error('FranceTravailService: Exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Normalize France Travail data and store in the jobs table
     */
    protected function normalizeAndStore(array $offres): array
    {
        $stored = [];

        foreach ($offres as $offre) {
            $title   = $offre['intitule'] ?? 'Poste inconnu';
            $company = $offre['entreprise']['nom'] ?? ($offre['entreprise']['entrepriseAdaptee'] ?? false ? 'Entreprise adaptée' : 'Entreprise confidentielle');

            // Skip duplicates
            $exists = Job::where('title', $title)
                ->where('company', $company)
                ->where('source', 'France Travail')
                ->exists();

            if ($exists) {
                continue;
            }

            // Extract skills from competences array
            $skills = $this->extractSkillsFromOffre($offre);

            // Build location
            $lieu = $offre['lieuTravail'] ?? [];
            $locationStr = $lieu['libelle'] ?? 'France';

            // Build salary
            $salary = $this->buildSalaryFromOffre($offre);

            // Contract type
            $contractType = $offre['typeContrat'] ?? null;
            $contractLabel = $this->mapContractType($contractType);

            // Description
            $description = $offre['description'] ?? '';
            $description = $this->cleanDescription($description);

            $job = Job::create([
                'title'           => $title,
                'company'         => $company,
                'description'     => $description,
                'skills_required' => $skills,
                'location'        => $locationStr,
                'salary'          => $salary,
                'source'          => 'France Travail',
                'contract_type'   => $contractLabel,
            ]);

            $stored[] = $job;
        }

        return $stored;
    }

    /**
     * Extract skills from France Travail competences
     */
    protected function extractSkillsFromOffre(array $offre): array
    {
        $skills = [];

        // From competences array
        if (!empty($offre['competences'])) {
            foreach ($offre['competences'] as $competence) {
                if (!empty($competence['libelle'])) {
                    $skills[] = $competence['libelle'];
                }
            }
        }

        // From qualitesProfessionnelles
        if (!empty($offre['qualitesProfessionnelles'])) {
            foreach ($offre['qualitesProfessionnelles'] as $qualite) {
                if (!empty($qualite['libelle'])) {
                    $skills[] = $qualite['libelle'];
                }
            }
        }

        return array_slice(array_unique($skills), 0, 8);
    }

    /**
     * Build salary string from France Travail data
     */
    protected function buildSalaryFromOffre(array $offre): ?string
    {
        $salaire = $offre['salaire'] ?? null;
        if (!$salaire) {
            return null;
        }

        if (!empty($salaire['libelle'])) {
            return $salaire['libelle'];
        }

        // Try complement
        if (!empty($salaire['complement1'])) {
            return $salaire['complement1'];
        }

        return null;
    }

    /**
     * Map contract type codes to readable labels
     */
    protected function mapContractType(?string $code): ?string
    {
        if (!$code) return null;

        $map = [
            'CDI' => 'CDI',
            'CDD' => 'CDD',
            'MIS' => 'Intérim',
            'SAI' => 'Saisonnier',
            'LIB' => 'Libéral',
            'REP' => 'Reprise',
            'FRA' => 'Franchise',
            'DIN' => 'CDI Intérimaire',
            'CCE' => 'Profession commerciale',
        ];

        return $map[$code] ?? $code;
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
