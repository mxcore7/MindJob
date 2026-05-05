<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'views_count')) {
                $table->integer('views_count')->default(0);
            }
            if (!Schema::hasColumn('jobs', 'is_remote')) {
                $table->boolean('is_remote')->default(false);
            }
            if (!Schema::hasColumn('jobs', 'salary_min')) {
                $table->integer('salary_min')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'salary_max')) {
                $table->integer('salary_max')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'salary_currency')) {
                $table->string('salary_currency', 3)->default('EUR');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['views_count', 'is_remote', 'salary_min', 'salary_max', 'salary_currency']);
        });
    }
};