<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Candidate\CandidateJobController;
use App\Models\Candidate;
use App\Models\Clinic;
use App\Models\FillinShift;
use App\Models\Recruiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CancellationNotificationTest extends TestCase
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

        Schema::create('candidates', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('profile')->nullable();
            $table->timestamps();
        });

        Schema::create('recruiters', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('clinics', function ($table) {
            $table->id();
            $table->unsignedBigInteger('recruiter_id');
            $table->string('name')->nullable();
            $table->string('profile')->nullable();
            $table->timestamps();
        });

        Schema::create('fillin_shifts', function ($table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('title');
            $table->string('status')->default('confirmed');
            $table->unsignedBigInteger('booked_candidate_id')->nullable();
            $table->timestamps();
        });

        Schema::create('fillin_shift_cancellation_requests', function ($table) {
            $table->id();
            $table->unsignedBigInteger('fillin_shift_id');
            $table->unsignedBigInteger('candidate_id');
            $table->unsignedBigInteger('clinic_id');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->string('status')->default('pending');
            $table->text('recruiter_remark')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function ($table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('recruiter_f_c_m_tokens', function ($table) {
            $table->id();
            $table->unsignedBigInteger('recruiter_id');
            $table->string('fcm_token');
            $table->timestamps();
        });

        Schema::create('candidate_f_c_m_tokens', function ($table) {
            $table->id();
            $table->unsignedBigInteger('candidate_id');
            $table->string('fcm_token');
            $table->timestamps();
        });
    }

    public function test_request_cancellation_creates_recruiter_notification(): void
    {
        $candidate = Candidate::create([
            'name' => 'Jane Candidate',
            'email' => 'candidate@example.com',
        ]);

        $recruiter = Recruiter::create([
            'name' => 'Recruiter One',
            'email' => 'recruiter@example.com',
        ]);

        $clinic = Clinic::create([
            'recruiter_id' => $recruiter->id,
            'name' => 'Clinic One',
        ]);

        $shift = FillinShift::create([
            'clinic_id' => $clinic->id,
            'title' => 'Dental Shift',
            'status' => 'confirmed',
            'booked_candidate_id' => $candidate->id,
        ]);

        $request = Request::create('/candidate/request-cancellation', 'POST', [
            'shift_id' => $shift->id,
            'reason' => 'Personal reason',
            'notes' => 'Need to cancel',
        ]);
        $request->setUserResolver(function () use ($candidate) {
            return $candidate;
        });

        $controller = new CandidateJobController();
        $response = $controller->requestCancellation($request);

        $this->assertTrue($response->getData()->status);

        $notification = DB::table('notifications')
            ->where('type', 'App\\Notifications\\RecruiterFCMNotification')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('cancellation_request', $notification->data);
        $this->assertStringContainsString('cancel', strtolower($notification->data));
    }
}
