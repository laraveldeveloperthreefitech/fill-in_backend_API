<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('recruiter_recent_searches')) {
            return;
        }

        $indexes = DB::select("SHOW INDEX FROM recruiter_recent_searches");
        $indexNames = collect($indexes)->pluck('Key_name')->filter()->unique()->values();

        if ($indexNames->contains('recruiter_recent_searches_term_unique')) {
            DB::statement('ALTER TABLE recruiter_recent_searches DROP INDEX recruiter_recent_searches_term_unique');
        }

        if (!$indexNames->contains('recruiter_recent_searches_recruiter_id_term_unique')) {
            DB::statement('ALTER TABLE recruiter_recent_searches ADD UNIQUE INDEX recruiter_recent_searches_recruiter_id_term_unique (recruiter_id, term)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('recruiter_recent_searches')) {
            return;
        }

        $indexes = DB::select("SHOW INDEX FROM recruiter_recent_searches");
        $indexNames = collect($indexes)->pluck('Key_name')->filter()->unique()->values();

        if ($indexNames->contains('recruiter_recent_searches_recruiter_id_term_unique')) {
            DB::statement('ALTER TABLE recruiter_recent_searches DROP INDEX recruiter_recent_searches_recruiter_id_term_unique');
        }

        if (!$indexNames->contains('recruiter_recent_searches_term_unique')) {
            DB::statement('ALTER TABLE recruiter_recent_searches ADD UNIQUE INDEX recruiter_recent_searches_term_unique (term)');
        }
    }
};
