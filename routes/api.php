<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\Recruiter\{RecruiterAuthController, RecruiterJobController, RecruiterClinicController, RecruiterApiController};
use App\Http\Controllers\Api\Candidate\{CandidateAuthController, CandidateJobController, CandidateApiController};
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\BranchController;


Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate'])
    ->middleware('broadcast.jwt.auth');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::controller(CommonController::class)->group(function () {
    Route::get('dashboard', 'dashboard');
});


Route::prefix('recruiter')->group(function () {
    Route::controller(RecruiterAuthController::class)->group(function () {
        Route::any('login', 'login');
        Route::post('registraion', 'register');
        Route::post('send-otp', 'sendOtp');
        Route::post('verify-otp', 'verifyOtp');
        Route::post('change-password', 'changePassword');
    });
    Route::controller(RecruiterApiController::class)->group(function () {
        Route::get('faq', 'Faq');
        Route::get('search-terms', 'searchTerms');
    });
    Route::controller(RecruiterJobController::class)->group(function () {
        Route::get('view-applicants/{id}', 'viewCandidate');
    });
    Route::controller(CommonController::class)->group(function () {
        Route::get('get-specilization/{id}', 'getSpecialization');
        Route::get('setting', 'setting');
        Route::post('get-dropdown-data', 'getDropdownData');
    });
    Route::middleware(['auth:recruiter'])->group(function () {
        // Route::get('/notifications/mark-as-read/{id}', function ($id) {
        //     $notification = Auth::guard('recruiter')->user()->notifications()->findOrFail($id);
        //     $notification->markAsRead();
        //     return response()->json(['status' => 'read']);
        // });

        // Get recruiter notifications
        Route::get('/notifications', function () {

            $notifications = Auth::guard('recruiter')
                ->user()
                ->notifications()
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'data' => $notifications
            ]);
        });




        // Mark single notification as read
        Route::get('/notifications/mark-as-read/{id}', function ($id) {

            $notification = Auth::guard('recruiter')
                ->user()
                ->notifications()
                ->findOrFail($id);

            $notification->markAsRead();

            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read'
            ]);
        });




        // Mark all as read
        Route::get('/notifications/mark-all-read', function () {

            Auth::guard('recruiter')
                ->user()
                ->unreadNotifications
                ->markAsRead();

            return response()->json([
                'status' => true,
                'message' => 'All notifications marked as read'
            ]);
        });




        Route::controller(RecruiterClinicController::class)->group(function () {
            Route::post('create-update-clinic', 'createClinic');
            Route::get('view-clinic', 'viewClinic');
        });
        Route::controller(RecruiterAuthController::class)->group(function () {
            Route::post('update-profile', 'updateProfile');
            Route::get('view-profile', 'viewProfile');
        });
        Route::controller(RecruiterApiController::class)->group(function () {
            Route::post('support', 'SupportInfo');
            Route::get('faq', 'Faq');
            Route::post('change-email', 'changeEmail');
            Route::get('notification-list', 'NotificationList');
            Route::get('chat-users', 'recruiterChatUser');
        });

        Route::controller(CommonController::class)->group(function () {

            Route::post('change-phone', 'changePhone');
        });
        Route::middleware(['clinic'])->group(function () {
            Route::controller(RecruiterJobController::class)->group(function () {
                Route::post('create-job', 'createJobs');
                Route::get('job-list', 'jobList');
                Route::get('job-detail/{id}', 'viewjob');
                Route::get('all-candidate', 'allCandidates');
                Route::get('candidate-applied-jobs/{id}', 'candidateAppliedJobs');
                Route::post('delete-jobs/{id}', 'delete');
                Route::get('job-candidates/{id}', 'jobCandidates');

                Route::get('/recruiter-calendar', 'recruiterCalendar');

                Route::post('update-job-status/{id}', 'updateJobStatus');
                Route::post('schedule-interview', 'scheduleInterView');
                Route::post('reschedule-interview/{id}', 'rescheduleInterview');
                Route::get('interview-list', 'InterViewList');
                Route::get('interView-details/{id}', 'InterViewDetails');
                Route::get('complete-interview/{id}', 'completeInterview');

                //Added by Sr.
                Route::get('/calendar', 'calendar');
            });
            Route::controller(RecruiterApiController::class)->group(function () {
                Route::post('add-review', 'addCandidateRating');
                Route::get('feedback-questions', 'candidateFeedbackQuestions');
                Route::get('candidate-ranking', 'candidateRanking');
                Route::get('candidate-ranking/{candidateId}', 'candidateRankingDetail');
                Route::post('report', 'reportToCandidate');
                Route::post('support', 'SupportInfo');
                Route::get('candidate-by-profession', 'candidateByProfession');
            });
            Route::controller(CommonController::class)->group(function () {

                Route::get('profession', 'profession');
                Route::post('chat', 'sendMessage');
                Route::get('chat-history/{id}', 'allChat');
                Route::get('recruiter-profile', 'recruiterProfile');
                Route::get('chat/mark-as-read/{id}', 'chatMarkAsRead');
            });


            Route::controller(RecruiterJobController::class)->group(function () {
                Route::post('/search-candidates', 'searchCandidates');
                Route::post('/check-availability', 'checkAvailability');
                Route::get('/available-responses/{shiftId}', 'availableResponses');
                Route::post('/confirm-booking', 'confirmBooking');

                // New API
                Route::get('/my-bookings', 'myFillinBookings');
                /*
                |--------------------------------------------------------------------------
                | Cancellation Requests
                |--------------------------------------------------------------------------
                */

                Route::get('/cancellation-requests', 'cancellationRequests');

                Route::get('/cancellation-request/{id}', 'cancellationRequestDetail');

                Route::post('/update-cancellation-status', 'updateCancellationStatus');
                
                Route::post('/confirm-shift-completion', 'confirmShiftCompletion');
                Route::get('/completed-fillin-shifts', 'completedFillinShifts');
            });

           
                 Route::resource('branches', BranchController::class);
        });
    });
});

Route::prefix('candidate')->group(function () {
    Route::controller(CandidateAuthController::class)->group(function () {
        Route::any('login', 'login');
        Route::post('registraion', 'register');
        Route::post('send-otp', 'sendOtp');
        Route::post('verify-otp', 'verifyOtp');
        Route::post('change-password', 'changePassword');
    });

    Route::controller(CandidateJobController::class)->group(function () {
        Route::get('jobs', 'candidateJob');
        Route::get('view-job/{id}', 'viewJob');
    });
    Route::controller(CandidateApiController::class)->group(function () {
        Route::get('faq', 'Faq');
        Route::get('view-clinic/{id}', 'viewClinic');
    });
    Route::controller(CommonController::class)->group(function () {
        Route::get('get-dropdown-data', 'getDropdownData');
        Route::get('get-specilization/{id}', 'getSpecialization');
        Route::get('setting', 'setting');
    });

    Route::middleware(['auth:candidate'])->group(function () {
        // Route::get('notifications/mark-as-read/{id}', function ($id) {
        //     $notification = Auth::guard('candidate')->user()->notifications()->findOrFail($id);
        //     $notification->markAsRead();
        //     return response()->json(['status' => '']);
        // });

        // Get candidate notifications
        Route::get('/notifications', function () {

            $notifications = Auth::guard('candidate')
                ->user()
                ->notifications()
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'data' => $notifications
            ]);
        });




        // Mark single notification as read
        Route::get('/notifications/mark-as-read/{id}', function ($id) {

            $notification = Auth::guard('candidate')
                ->user()
                ->notifications()
                ->findOrFail($id);

            $notification->markAsRead();

            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read'
            ]);
        });




        // Mark all as read
        Route::get('/notifications/mark-all-read', function () {

            Auth::guard('candidate')
                ->user()
                ->unreadNotifications
                ->markAsRead();

            return response()->json([
                'status' => true,
                'message' => 'All notifications marked as read'
            ]);
        });

        Route::controller(CandidateAuthController::class)->group(function () {
            Route::post('update-profile', 'updateProfile');
            Route::get('view-profile', 'viewProfile');
        });
        Route::controller(CandidateJobController::class)->group(function () {
            Route::post('apply-jobs/{id}', 'applyJobs');
            Route::post('action-on-interview/{id}', 'actionOnInterview');
            Route::get('jobs', 'candidateJob');
            Route::get('applied-jobs', 'appliedJobs');
            Route::post('bookmarked/{id}', 'bookmarked');
            Route::post('remove-bookmarked/{id}', 'removeBookmarked');
            Route::get('bookmarked-list', 'bookmarkedList');
            Route::get('interview-list', 'InterViewList');
            Route::get('interView-details/{id}', 'InterViewDetails');
        });
        Route::controller(CandidateApiController::class)->group(function () {
            Route::post('add-review', 'addClinicRating');
            Route::get('feedback-questions', 'clinicFeedbackQuestions');
            Route::get('recruiter-ranking', 'recruiterRanking');
            Route::get('recruiter-ranking/{recruiterId}', 'recruiterRankingDetail');
            Route::post('report', 'reportToJob');
            Route::post('report-on-recruiter', 'reportToRecruiter');
            Route::post('support', 'SupportInfo');
            Route::get('faq', 'Faq');
            Route::post('change-email', 'changeEmail');
            Route::get('job-by-Profession', 'jobByProfession');
            Route::get('notification-list', 'NotificationList');
            Route::get('chat-users', 'candidateChatUser');
            Route::get('search-terms', 'searchTerms');
        });
        Route::controller(CommonController::class)->group(function () {
            Route::get('get-specilization/{id}', 'getSpecialization');
            Route::post('change-phone', 'changePhone');
            Route::get('profession', 'profession');
            Route::get('candidate-profile', 'candidateProfile');
            Route::any('chat', 'sendMessage');
            Route::get('chat-history/{id}', 'allChat');
            Route::get('chat/mark-as-read/{id}', 'chatMarkAsRead');
            Route::get('vaccinations', 'vaccinations');
        });




            Route::controller(CandidateJobController::class)->group(function () {
                Route::get('/shift-detail/{shiftId}', 'shiftDetail');
                Route::post('/respond-availability', 'respondAvailability');
                // New API
                Route::get('/my-bookings', 'myFillinBookings');
                //addes by sr.
                Route::get('/calendar', 'calendar');
                /*
                |--------------------------------------------------------------------------
                | Cancellation Request
                |--------------------------------------------------------------------------
                */

                Route::post('/request-cancellation', 'requestCancellation');

                Route::get('/cancellation-requests', 'myCancellationRequests');

                Route::get('/cancellation-request/{id}', 'cancellationRequestDetail');

                Route::get('/branch', 'branch');

                Route::post('/complete-shift', 'completeShift');
                Route::get('/completed-fillin-shifts', 'completedFillinShifts');
            });
    });
});


Route::get('language', [CommonController::class, 'language']);
