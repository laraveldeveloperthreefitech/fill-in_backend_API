<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Recruiter;
use App\Models\RecruiterRecentSearch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RecruiterRecentSearchDuplicateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);

        DB::purge('sqlite');

        Schema::create('recruiters', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('recruiter_recent_searches', function ($table) {
            $table->id();
            $table->unsignedBigInteger('recruiter_id');
            $table->string('term');
            $table->timestamps();
            $table->unique(['recruiter_id', 'term']);
        });
    }

    public function test_same_term_can_exist_for_different_recruiters(): void
    {
        $recruiterOne = Recruiter::create(['name' => 'Recruiter One']);
        $recruiterTwo = Recruiter::create(['name' => 'Recruiter Two']);

        RecruiterRecentSearch::create(['recruiter_id' => $recruiterOne->id, 'term' => 'Dental Assistant']);
        RecruiterRecentSearch::create(['recruiter_id' => $recruiterTwo->id, 'term' => 'Dental Assistant']);

        $this->assertEquals(2, RecruiterRecentSearch::where('term', 'Dental Assistant')->count());
    }
}
