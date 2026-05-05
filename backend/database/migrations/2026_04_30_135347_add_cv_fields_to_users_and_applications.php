<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter CV à la table users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cv_path')) {
                $table->string('cv_path')->nullable();
            }
            if (!Schema::hasColumn('users', 'cover_letter_path')) {
                $table->string('cover_letter_path')->nullable();
            }
            if (!Schema::hasColumn('users', 'cv_uploaded_at')) {
                $table->timestamp('cv_uploaded_at')->nullable();
            }
        });
        
        // Ajouter CV/lettre par candidature
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'application_cv_path')) {
                $table->string('application_cv_path')->nullable();
            }
            if (!Schema::hasColumn('job_applications', 'application_cover_letter_path')) {
                $table->string('application_cover_letter_path')->nullable();
            }
        });
    }
    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cv_path', 'cover_letter_path', 'cv_uploaded_at']);
        });
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['application_cv_path', 'application_cover_letter_path']);
        });
    }
};