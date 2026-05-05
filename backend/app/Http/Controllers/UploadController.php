<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class UploadController extends Controller
{
    /**
     * Upload du CV de l'utilisateur (profil)
     * POST /api/upload/cv
     */
    public function uploadResume(Request $request)
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf|max:5120', // Max 5MB
        ]);
        
        $user = $request->user();
        
        // Supprimer l'ancien CV si existant
        if ($user->cv_path && Storage::disk('public')->exists($user->cv_path)) {
            Storage::disk('public')->delete($user->cv_path);
        }
        
        // Stocker le nouveau CV
        $path = $request->file('cv')->store('cvs/' . $user->id, 'public');
        
        // Extraire les compétences du PDF
        $skillsFromCV = $this->extractSkillsFromPDF($request->file('cv')->path());
        
        // Mettre à jour l'utilisateur
        $user->update([
            'cv_path' => $path,
            'cv_uploaded_at' => now(),
            'skills' => $this->mergeSkills($user->skills, $skillsFromCV)
        ]);
        
        return response()->json([
            'message' => 'CV uploadé avec succès',
            'path' => Storage::disk('public')->url($path),
            'extracted_skills' => $skillsFromCV,
            'all_skills' => $user->skills
        ]);
    }
    
    /**
     * Upload de la lettre de motivation (profil)
     * POST /api/upload/cover-letter
     */
    public function uploadCoverLetter(Request $request)
    {
        $request->validate([
            'cover_letter' => 'required|file|mimes:pdf|max:5120'
        ]);
        
        $user = $request->user();
        
        if ($user->cover_letter_path && Storage::disk('public')->exists($user->cover_letter_path)) {
            Storage::disk('public')->delete($user->cover_letter_path);
        }
        
        $path = $request->file('cover_letter')->store('cover_letters/' . $user->id, 'public');
        
        $user->update(['cover_letter_path' => $path]);
        
        return response()->json([
            'message' => 'Lettre de motivation uploadée',
            'path' => Storage::disk('public')->url($path)
        ]);
    }
    
    /**
     * Upload CV + Lettre pour une candidature spécifique
     * POST /api/upload/application/{applicationId}
     */
    public function uploadApplicationFiles(Request $request, $applicationId)
    {
        $request->validate([
            'cv' => 'nullable|file|mimes:pdf|max:5120',
            'cover_letter' => 'nullable|file|mimes:pdf|max:5120'
        ]);
        
        $application = JobApplication::findOrFail($applicationId);
        
        // Vérifier que la candidature appartient à l'utilisateur
        if ($application->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
        
        $data = [];
        
        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('applications/' . $application->user_id . '/' . $application->id, 'public');
            $data['application_cv_path'] = $path;
        }
        
        if ($request->hasFile('cover_letter')) {
            $path = $request->file('cover_letter')->store('applications/' . $application->user_id . '/' . $application->id, 'public');
            $data['application_cover_letter_path'] = $path;
        }
        
        $application->update($data);
        
        return response()->json([
            'message' => 'Fichiers ajoutés à la candidature',
            'cv_path' => $data['application_cv_path'] ?? null,
            'cover_letter_path' => $data['application_cover_letter_path'] ?? null
        ]);
    }
    
    /**
     * Extraire les compétences d'un PDF
     */
    private function extractSkillsFromPDF($filePath): array
    {
        $skillsList = [
            'PHP', 'Laravel', 'Symfony', 'CodeIgniter', 'CakePHP',
            'Python', 'Django', 'Flask', 'FastAPI',
            'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Angular', 'Node.js',
            'Java', 'Spring Boot', 'C#', '.NET', 'Go', 'Rust', 'Ruby',
            'SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'Redis', 'Elasticsearch',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'Terraform',
            'Git', 'CI/CD', 'Jenkins', 'GitLab CI', 'GitHub Actions',
            'REST API', 'GraphQL', 'WebSocket',
            'HTML', 'CSS', 'Tailwind', 'Bootstrap', 'Sass',
            'Machine Learning', 'Data Science', 'AI', 'NLP', 'TensorFlow', 'PyTorch',
            'Flutter', 'React Native', 'Swift', 'Kotlin',
            'Agile', 'Scrum', 'Kanban', 'Jira', 'Confluence'
        ];
        
        try {
            // Vérifier si le parser est installé
            if (!class_exists(Parser::class)) {
                Log::warning('PDF Parser not installed. Run: composer require smalot/pdfparser');
                return [];
            }
            
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            $found = [];
            foreach ($skillsList as $skill) {
                if (stripos($text, $skill) !== false) {
                    $found[] = $skill;
                }
            }
            
            return array_slice(array_unique($found), 0, 15);
            
        } catch (\Exception $e) {
            Log::error('PDF parsing error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Fusionner les compétences existantes avec les nouvelles
     */
    private function mergeSkills($existingSkills, array $newSkills): string
    {
        $existing = [];
        if (is_string($existingSkills) && !empty($existingSkills)) {
            $existing = array_map('trim', explode(',', $existingSkills));
        } elseif (is_array($existingSkills)) {
            $existing = $existingSkills;
        }
        
        $all = array_unique(array_merge($existing, $newSkills));
        $all = array_filter($all);
        
        return implode(', ', $all);
    }
    
    /**
     * Télécharger le CV d'un utilisateur (pour l'employeur/recruteur)
     * GET /api/download/cv/{userId}
     */
    public function downloadCv($userId)
    {
        $user = User::findOrFail($userId);
        
        if (!$user->cv_path || !Storage::disk('public')->exists($user->cv_path)) {
            return response()->json(['error' => 'CV non trouvé'], 404);
        }
        
        return Storage::disk('public')->download($user->cv_path);
    }
    
    /**
     * Supprimer le CV de l'utilisateur
     * DELETE /api/upload/cv
     */
    public function deleteResume(Request $request)
    {
        $user = $request->user();
        
        if ($user->cv_path && Storage::disk('public')->exists($user->cv_path)) {
            Storage::disk('public')->delete($user->cv_path);
        }
        
        $user->update([
            'cv_path' => null,
            'cv_uploaded_at' => null
        ]);
        
        return response()->json(['message' => 'CV supprimé avec succès']);
    }
}