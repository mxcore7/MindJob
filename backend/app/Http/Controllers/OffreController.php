<?php

namespace App\Http\Controllers;

use App\Services\FranceTravailService;
use Illuminate\Http\Request;

class OffreController extends Controller
{
    public function __construct(private FranceTravailService $ftService) {}

    /**
     * Recherche live d'offres France Travail (non stockées)
     */
    public function index(Request $request)
    {
        $params = array_filter([
            'motsCles'     => $request->input('q'),
            'commune'      => $request->input('commune'),
            'departement'  => $request->input('departement'),
            'typeContrat'  => $request->input('contrat'),
            'experience'   => $request->input('experience'),
            'range'        => $request->input('range', '0-19'),
        ]);

        try {
            $data = $this->ftService->searchOffres($params);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Détail d'une offre France Travail
     */
    public function show(string $id)
    {
        try {
            $offre = $this->ftService->getOffre($id);
            return response()->json($offre);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
