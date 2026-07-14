<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Software;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardSearchSoftwareTest extends TestCase
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
        DB::connection('sqlite')->getSchemaBuilder()->create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialization_name')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        DB::connection('sqlite')->getSchemaBuilder()->create('software', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        DB::connection('sqlite')->getSchemaBuilder()->create('candidate_software', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_id');
            $table->unsignedBigInteger('software_id');
            $table->timestamps();
        });

        DB::connection('sqlite')->getSchemaBuilder()->create('recruiter_searches', function (Blueprint $table) {
            $table->id();
            $table->string('term');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();
        });

        DB::connection('sqlite')->getSchemaBuilder()->create('recruiter_recent_searches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recruiter_id');
            $table->string('term');
            $table->timestamps();
        });
    }

    public function test_api_search_matches_software_names_for_dashboard(): void
    {
        $candidate = Candidate::create([
            'name' => 'Jane Doe',
            'specialization_name' => 'Dentist',
            'address' => 'London',
        ]);

        $software = Software::create([
            'name' => 'Dentally',
            'status' => 1,
        ]);

        $candidate->software_experiance()->attach($software->id);

        $request = Request::create('/dashboard', 'GET', ['search' => 'Dentally']);

        $results = Candidate::ApiSearch($request)->get();

        $this->assertTrue($results->contains('id', $candidate->id));
    }
}
