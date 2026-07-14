<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JobListing, ScheduleInterview, Clinic, Recruiter};
use App\Http\Traits\{RestResponse, HelperTrait};
use App\Http\Resources\Candidate\{JobListResource};
use App\Http\Resources\{ScheduleInterviewResource, InterviewListResource};
use App\Services\FirebaseNotificationService;
use App\Models\{FillinShift, FillinShiftResponse, FillinShiftCancellationRequest, Candidate};
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Software;
use App\Models\Branch;


class CandidateJobController extends Controller
{
    use RestResponse, HelperTrait;

    protected $fcm;

    public function __construct()
    {
        $this->fcm = new FirebaseNotificationService();
    }
    /**
     * candidateJob
     * Developer : Faizan Khan                                                                                       
     * @param  mixed $request
     * @return void
     */
    public function candidateJob(Request $request)
    {
        try {
            $data = JobListing::ApiSearch($request)->orderByDesc('id')->with('clinic', 'specialization', 'employmentTypes')
                ->withCount('candidates')->whereStatus(1)->paginate($this->requestLimit($request));
            if (count($data))
                return $this->recordListFoundWithResponse((JobListResource::collection($data)->response()->getData()));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * applyJobs
     *
     * @param  mixed $id
     * @return void
     */
    //   public function applyJobs($id){
    //     try{

    //         $jobListing = JobListing::find($id);
    //         if ($jobListing) {
    //             $jobListing->candidates()->sync([
    //                 auth()->id() => ['status' => 'Application Sent']
    //             ], false); 
    //             $this->sendNotification($jobListing);

    //             return $this->customSuccessRes('Apply Successfully');
    //         }
    //         else
    //             return $this->recordNotFoundResponse();
    //     }catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }


    public function applyJobs($id)
    {
        try {
            $jobListing = JobListing::find($id);

            if (!$jobListing) {
                return $this->recordNotFoundResponse();
            }

            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'statusCode' => 0,
                    'status' => 'failed',
                    'message' => 'Unauthenticated user'
                ], 401);
            }

            $jobListing->candidates()->syncWithoutDetaching([
                $userId => [
                    'status' => 'Application Sent'
                ]
            ]);

            try {
                $this->sendNotification($jobListing);
            } catch (\Exception $notificationException) {
                \Log::error('Notification failed: ' . $notificationException->getMessage());
            }

            return $this->customSuccessRes('Apply Successfully');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }


    private function sendNotification($jobListing)
    {
        $user = auth()->guard('candidate')->user();
        $icon = $user->profile ? $user->profile : '';
        $this->fcm->notifyRecruiters(
            Clinic::where('id', $jobListing->clinic_id)->value('recruiter_id'),
            $user->name . ' has applied to your job posting: ' . $jobListing->title,
            'New Job Application Received',
            '',
            $icon,
            'apply jobs',
            $jobListing->id,
            'candidate'
        );
    }

    /**
     * appliedJobs
     *
     * @return void
     */
    public function appliedJobs()
    {
        try {
            $data = auth()->user()->job()->withCount('candidates')
                ->orderByDesc('id')->get();
            if (count($data) > 0)
                return $this->recordFoundWithResponse(JobListResource::collection($data));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }


    /**
     * viewJob
     *
     * @param  mixed $id
     * @return void
     */
    public function viewJob($id)
    {
        try {
            $data = JobListing::withCount('candidates')->find($id);
            if ($data)
                return $this->recordFoundWithResponse(new JobListResource($data));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * InterViewList
     *
     * @param  mixed $request
     * @return void
     */
    public function InterViewList(Request $request)
    {
        try {
            $data = ScheduleInterview::with('candidate', 'job')->orderByDesc('id')
                ->where('candidate_id', auth()->user()->id)->get();
            if (count($data) > 0)
                return $this->recordFoundWithResponse(InterviewListResource::collection($data));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * InterViewDetails
     *
     * @param  mixed $id
     * @return void
     */
    public function InterViewDetails($id)
    {
        try {
            $data = ScheduleInterview::with('candidate', 'job')->find($id);
            if ($data)
                return $this->recordFoundWithResponse(new ScheduleInterviewResource($data));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * bookmarked
     *
     * @param  mixed $id
     * @return void
     */
    public function bookmarked($id)
    {
        try {
            $jobListing = JobListing::find($id);
            if ($jobListing) {
                $jobListing->bookmarked()->sync(auth()->id());
                return $this->customSuccessRes('Add to bookmarked');
            } else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    /**
     * bookmarkedList
     *
     * @return void
     */
    public function bookmarkedList()
    {
        try {
            $data = auth()->user()->bookmarked()->orderByDesc('id')->get();
            if (count($data))
                return $this->recordFoundWithResponse(JobListResource::collection($data));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * removeBookmarked
     *
     * @param  mixed $id
     * @return void
     */
    public function removeBookmarked($id)
    {
        try {
            $jobListing = JobListing::find($id);
            if ($jobListing) {
                $jobListing->bookmarked()->detach(auth()->id());
                return $this->customSuccessRes('Removed from Bookmarked');
            } else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    public function actionOnInterview(Request $request, $id)
    {
        try {
            $interview = ScheduleInterview::find($id);

            if ($interview) {
                $interview->update(['interview_status' => $request->status]);
                $this->sendInterViewNotification($interview, $request);
                return $this->customSuccessRes('Interview status changed successfully.');
            } else {
                return $this->recordNotFoundResponse();
            }
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    private function sendInterViewNotification($interview, $request)
    {
        $user   = auth()->guard('candidate')->user();
        $icon   = $user->profile ?? '';

        // status message
        $statusText = $request->status == 1 ? 'accepted' : 'rejected';

        $this->fcm->notifyRecruiters(
            $interview->clinic->recruiter_id,
            "Candidate {$user->name} has {$statusText} your interview request", // 👈 dynamic message
            "Interview {$statusText}",                                          // 👈 title
            '',
            $icon,
            "interview_{$statusText}",                                          // 👈 action key
            $interview->id,
            'candidate'
        );
    }

    // public function shiftDetail(Request $request, $shiftId)
    // {
    //     try {
    //         $candidate = $request->user();

    //         if (!$candidate) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Unauthenticated candidate',
    //             ], 401);
    //         }

    //         $response = FillinShiftResponse::where('fillin_shift_id', $shiftId)
    //             ->where('candidate_id', $candidate->id)
    //             ->first();

    //         if (!$response) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Shift request not found for this candidate',
    //             ], 404);
    //         }

    //         $shift = FillinShift::with([
    //             'clinic.recruiter',
    //             'specialization'
    //         ])->find($shiftId);

    //         if (!$shift) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Shift not found',
    //             ], 404);
    //         }

    //         $clinic = $shift->clinic;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Shift detail fetched successfully',
    //             'data' => [
    //                 'id' => $shift->id,
    //                 'title' => $shift->title,

    //                 'practice_name' => optional($clinic)->name,

    //                 'specialization_id' => $shift->specialization_id,
    //                 'specialization' => optional($shift->specialization)->name,

    //                 'experiance_level' => $shift->experiance_level,

    //                 'software' => $shift->software,
    //                 'vacancy' => $shift->vacancy,
    //                 'urgent' => $shift->urgent,

    //                 'hourly_rate' => (string) $shift->hourly_rate,

    //                 'description' => $shift->job_description,
    //                 'benefits' => $shift->benefits,

    //                 'expire_date' => $shift->expire_date,

    //                 'city' => $shift->city,
    //                 'address' => $shift->address,
    //                 'short_address' => $shift->short_address,

    //                 'latitude' => $shift->latitude,
    //                 'longitude' => $shift->longitude,

    //                 'practice_profile' => (
    //                     $clinic &&
    //                     $clinic->recruiter &&
    //                     $clinic->recruiter->profile
    //                 )
    //                     ? config('filepaths.recruiter.public_url') . $clinic->recruiter->profile
    //                     : null,

    //                 'shift_date' => $shift->shift_date,

    //                 'start_time' => $shift->start_time
    //                     ? date('H:i:s', strtotime($shift->start_time))
    //                     : null,

    //                 'end_time' => $shift->end_time
    //                     ? date('H:i:s', strtotime($shift->end_time))
    //                     : null,

    //                 'candidate_id' => $candidate->id,

    //                 'response' => $response->response,
    //                 'responded_at' => $response->responded_at,
    //             ],
    //         ], 200);

    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //         ], 500);
    //     }
    // }

    public function shiftDetail(Request $request, $shiftId)
    {
        try {
            $candidate = $request->user();

            if (!$candidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated candidate',
                ], 401);
            }

            $response = FillinShiftResponse::where('fillin_shift_id', $shiftId)
                ->where('candidate_id', $candidate->id)
                ->first();

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift request not found for this candidate',
                ], 404);
            }

            $shift = FillinShift::with([
                'clinic.recruiter',
                'specialization'
            ])->find($shiftId);

            $isMyBooking = $shift->status === 'confirmed' &&
               $shift->booked_candidate_id == $candidate->id ? 1 : 0;

            if (!$shift) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift not found',
                ], 404);
            }

            $clinic = $shift->clinic;
            $recruiter = optional($clinic)->recruiter;

            // Software Names
            $softwareNames = [];

            if (!empty($shift->software) && is_array($shift->software)) {
                $softwareNames = Software::whereIn('id', $shift->software)
                    ->pluck('name')
                    ->values()
                    ->toArray();
            }

            return response()->json([
                'success' => true,
                'message' => 'Shift detail fetched successfully',
                'data' => [
                    'id' => $shift->id,
                    'title' => $shift->title,

                    // P1
                    'clinic_id' => $clinic?->id,
                    'recruiter_id' => $recruiter?->id,

                    'practice_name' => $clinic?->name,

                    'specialization_id' => $shift->specialization_id,
                    'specialization' => optional($shift->specialization)->name,

                    'experiance_level' => $shift->experiance_level,

                    // P3
                    'software' => $shift->software,
                    'software_names' => $softwareNames,
                    'other_software' => $shift->other_software,

                    'vacancy' => $shift->vacancy,
                    'urgent' => $shift->urgent,

                    'hourly_rate' => (string) $shift->hourly_rate,

                    'description' => $shift->job_description,
                    'benefits' => $shift->benefits,

                    'expire_date' => $shift->expire_date,

                    // 'city' => $shift->city,
                    'address' => $shift->address,
                    'short_address' => $shift->short_address,

                    'latitude' => $shift->latitude,
                    'longitude' => $shift->longitude,

                    'practice_profile' => (
                        $recruiter &&
                        $recruiter->profile
                    )
                        ? config('filepaths.recruiter.public_url') . $recruiter->profile
                        : null,

                    // P2
                    'shift_date' => $shift->shift_date
                        ? date('Y-m-d', strtotime($shift->shift_date))
                        : null,

                    'start_time' => $shift->start_time
                        ? date('H:i', strtotime($shift->start_time))
                        : null,

                    'end_time' => $shift->end_time
                        ? date('H:i', strtotime($shift->end_time))
                        : null,

                    'candidate_id' => $candidate->id,
                    'response' => $response->response,
                    
                    // 'responded_at' => $response->responded_at,
                    'responded_at' => $shift->responded_at
                        ? date('Y-m-d', strtotime($shift->responded_at))
                        : null,
                    'is_my_booking' => $isMyBooking,

                    'created_at' => $shift->created_at,
                    'updated_at' => $shift->updated_at,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    // public function respondAvailability(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'shift_id' => 'required|exists:fillin_shifts,id',
    //             'response' => 'required|in:available,not_available',
    //         ]);

    //         $candidate = $request->user();

    //         $shiftResponse = FillinShiftResponse::where('fillin_shift_id', $request->shift_id)
    //             ->where('candidate_id', $candidate->id)
    //             ->first();

    //         if (!$shiftResponse) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Shift request not found for this candidate',
    //             ], 404);
    //         }

    //         $shiftResponse->update([
    //             'response' => $request->response,
    //             'responded_at' => now(),
    //         ]);

    //         $shift = FillinShift::with(['clinic', 'specialization'])
    //             ->find($request->shift_id);

    //         if (!$shift) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Shift not found',
    //             ], 404);
    //         }

    //         if ($request->response === 'available') {
    //             $date = Carbon::parse($shift->shift_date)->format('M d, Y');

    //             $this->sendPushNotification(
    //                 $shift->clinic->user ?? null,
    //                 'Candidate Available',
    //                 "{$candidate->name} is available for your {$date} shift",
    //                 [
    //                     'type' => 'fillin_candidate_available',
    //                     'screen' => 'FILLIN_AVAILABLE_RESPONSES',
    //                     'shiftId' => $shift->id,
    //                 ]
    //             );
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Response sent successfully',
    //             'data' => [
    //                 'shift_id' => $shift->id,
    //                 'candidate_id' => $candidate->id,
    //                 'response' => $shiftResponse->response,
    //                 'responded_at' => $shiftResponse->responded_at,
    //             ],
    //         ], 200);

    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation error',
    //             'errors' => $e->errors(),
    //         ], 422);

    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //         ], 500);
    //     }
    // }


    
    public function respondAvailability(Request $request)
{
try {

    $request->validate([
        'shift_id' => 'required|exists:fillin_shifts,id',
        'response' => 'required|in:available,not-available',
    ]);

    $candidate = $request->user();

    if (!$candidate) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated candidate',
        ], 401);
    }

    $shiftResponse = FillinShiftResponse::where('fillin_shift_id', $request->shift_id)
        ->where('candidate_id', $candidate->id)
        ->first();

    if (!$shiftResponse) {
        return response()->json([
            'success' => false,
            'message' => 'Shift request not found for this candidate',
        ], 404);
    }

    $shift = FillinShift::with([
        'clinic',
        'specialization',
    ])->find($request->shift_id);

    if (!$shift) {
        return response()->json([
            'success' => false,
            'message' => 'Shift not found',
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Candidate already responded
    |--------------------------------------------------------------------------
    */
    if (
        !empty($shiftResponse->response) &&
        in_array($shiftResponse->response, ['available', 'not-available'])
    ) {
        return response()->json([
            'success' => false,
            'message' => 'You have already responded to this shift. Response cannot be changed.',
            'data' => [
                'shift_id' => $shift->id,
                'candidate_id' => $candidate->id,
                'current_response' => $shiftResponse->response,
                'responded_at' => $shiftResponse->responded_at,
                //  'responded_at' => $shiftResponse->responded_at
                //     ? $shiftResponse->responded_at->format('Y-m-d')
                //     : null,
            ]
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Save candidate response
    |--------------------------------------------------------------------------
    */
    $shiftResponse->update([
        'response' => $request->response,
        'responded_at' => now(),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Notify recruiter
    |--------------------------------------------------------------------------
    */
    try {

        $this->sendAvailabilityNotification(
            $shift,
            $candidate,
            $request->response
        );

    } catch (\Exception $notificationException) {

        \Log::error(
            'Availability notification failed: ' .
            $notificationException->getMessage()
        );
    }

    $shiftResponse->refresh();

    return response()->json([
        'success' => true,
        'message' => 'Response submitted successfully',
        'data' => [
            'shift_id' => $shift->id,
            'title' => $shift->title,
            'candidate_id' => $candidate->id,
            'response' => $shiftResponse->response,
            'responded_at' => $shiftResponse->responded_at,
    //          'responded_at' => $shiftResponse->responded_at
    // ? $shiftResponse->responded_at->format('Y-m-d')
    // : null,
        ],
    ], 200);

} catch (ValidationException $e) {

    return response()->json([
        'success' => false,
        'message' => 'Validation error',
        'errors' => $e->errors(),
    ], 422);

} catch (\Throwable $e) {

    \Log::error('Respond Availability Error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], 500);
}

}


   public function myFillinBookings(Request $request)
{
try {

    $candidate = $request->user();

    $query = FillinShiftResponse::query()
        ->where('candidate_id', $candidate->id)
        ->with([
            'shift.specialization',
            'shift.clinic.recruiter',
        ]);

    $status = $request->get('status', 'all');

    if ($status !== 'all') {

        if ($status === 'confirmed') {

            $query->whereHas('shift', function ($q) use ($candidate) {
                $q->where('status', 'confirmed')
                    ->where('booked_candidate_id', $candidate->id);
            });

        } else {

            $query->where('response', $status);
        }
    }

    $responses = $query
        ->join(
            'fillin_shifts',
            'fillin_shift_responses.fillin_shift_id',
            '=',
            'fillin_shifts.id'
        )
        ->orderByDesc('fillin_shifts.created_at')
        ->select('fillin_shift_responses.*')
        ->get();

    $data = $responses->map(function ($response) use ($candidate) {

        $shift = $response->shift;

        if (!$shift) {
            return null;
        }

        $clinic = $shift->clinic;

        return [
            'shift_id' => $shift->id,
            'title' => $shift->title,

            'specialization' => optional(
                $shift->specialization
            )->name,

            'hourly_rate' => number_format(
                (float) $shift->hourly_rate,
                2,
                '.',
                ''
            ),

            'address' => $shift->address,
            'short_address' => $shift->short_address,
            'city' => $shift->city,
            'expire_date' => $shift->expire_date,

            'my_response' => $response->response ?? 'pending',

            'my_response_at' => $response->responded_at,

            'shift_status' => $this->getCandidateShiftStatus($shift),

            'is_my_booking' => (
                $shift->status === 'confirmed'
                && (int) $shift->booked_candidate_id === (int) $candidate->id
            ),

            'recruiter' => [
                'id' => $clinic->recruiter_id ?? null,
                'clinic_id' => $clinic->id ?? null,

                'practice_name' =>
                    $clinic->practice_name
                    ?? $clinic->name
                    ?? null,

                'profile' => !empty($clinic->profile)
                    ? config('filepaths.clinic.public_url')
                        . $clinic->profile
                    : null,

                'description' => $clinic->description ?? null,
                'address' => $clinic->address ?? null,
                'postcode' => $clinic->postcode ?? null,
                'phone' => $clinic->phone ?? null,
                'web_link' => $clinic->web_link ?? null,

                'established_year' =>
                    $clinic->established_year ?? null,

                'practice_size' =>
                    $clinic->practice_size ?? null,

                'working_hours' =>
                    $clinic->working_hours ?? null,

                'verification' =>
                    $clinic->verification ?? null,

                'status' => $clinic->status ?? null,

                'recruiter_name' =>
                    $clinic->recruiter->name ?? null,

                'recruiter_email' =>
                    $clinic->recruiter->email ?? null,

                'recruiter_profile' =>
                    !empty($clinic->recruiter->profile)
                    ? config('filepaths.recruiter.public_url')
                        . $clinic->recruiter->profile
                    : null,
            ],

            'created_at' => $shift->created_at,
        ];
    })->filter()->values();

    return response()->json([
        'success' => true,
        'message' => 'My fill-in bookings fetched successfully',
        'total' => $data->count(),
        'data' => $data,
    ], 200);

} catch (\Throwable $e) {

    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
    ], 500);
}


}


    private function getCandidateShiftStatus($shift)
    {
        if ($shift->status === 'confirmed') {
            return 'confirmed';
        }

        if ($shift->status === 'cancelled') {
            return 'cancelled';
        }

        if ($shift->expire_date && now()->toDateString() > $shift->expire_date) {
            return 'expired';
        }

        return 'not-confirmed';
    }


    private function sendAvailabilityNotification($shift, $candidate, string $response)
    {
        $icon = $candidate->profile ? $candidate->profile : '';

        $recruiterId = Clinic::where('id', $shift->clinic_id)->value('recruiter_id');

        if (!$recruiterId) {
            return;
        }

        if ($response === 'available') {
            $title = 'Candidate Available';
            $message = $candidate->name . ' is available for your shift: ' . $shift->title;
            $type = 'fillin available';
        } else {
            $title = 'Candidate Not Available';
            $message = $candidate->name . ' is not available for your shift: ' . $shift->title;
            $type = 'fillin not available';
        }

        $this->fcm->notifyRecruiters(
            $recruiterId,
            $message,
            $title,
            '',
            $icon,
            $type,
            $shift->id,
            'candidate'
        );
    }


    public function calendar(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date',
        ]);

        $candidate = $request->user();

        $responses = FillinShiftResponse::with([
            'shift.clinic.recruiter'
        ])
            ->where('candidate_id', $candidate->id)
            ->whereHas('shift', function ($q) use ($request) {
                $q->whereBetween('shift_date', [
                    $request->from,
                    $request->to
                ]);
            })
            ->get();

        $data = $responses->map(function ($response) {

            $shift = $response->shift;

            if (!$shift) {
                return null;
            }

            if (
                $shift->status === 'confirmed' &&
                $shift->booked_candidate_id == $response->candidate_id
            ) {
                $status = 'confirmed';
            } elseif ($response->response === 'available') {
                $status = 'accepted';
            } elseif ($response->response === 'not-available') {
                $status = 'declined';
            } else {
                $status = 'requested';
            }

            return [
                'id' => $shift->id,
                'title' => $shift->title,
                'practice_name' => optional($shift->clinic)->name,
                'recruiter_id' => optional(
                    optional($shift->clinic)->recruiter
                )->id,
                // 'date' => $shift->shift_date,
                'date' => date('Y-m-d', strtotime($shift->shift_date)),
                'start_time' => date('H:i', strtotime($shift->start_time)),
                'end_time' => date('H:i', strtotime($shift->end_time)),
                'status' => $status,
            ];
        })->filter()->values();

        return response()->json([
            'status' => true,
            'message' => 'Calendar fetched',
            'data' => $data,
        ]);
    }
    
//   public function completeShift(Request $request)
// {
//     try {

//         $validated = $request->validate([
//             'shift_id' => 'required|exists:fillin_shifts,id',
//         ]);

//         $candidate = $request->user();

//         $shift = FillinShift::find($validated['shift_id']);

//         if (!$shift) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Shift not found.'
//             ],404);
//         }

//         // Candidate must be booked
//         if ((int)$shift->booked_candidate_id !== (int)$candidate->id) {

//             return response()->json([
//                 'success' => false,
//                 'message' => 'This shift is not assigned to you.'
//             ],403);
//         }

//         // Booking must be confirmed
//         if ($shift->status != 'confirmed') {

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Only confirmed shifts can be completed.'
//             ],422);
//         }

//         // Already completed
//         if ($shift->candidate_completed) {

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Shift already marked as completed.'
//             ],422);
//         }

//         $shift->update([
//             'candidate_completed' => 1,
//             'candidate_completed_at' => now(),
//         ]);

//         // Optional
//         // Notify recruiter here

//         return response()->json([
//             'success' => true,
//             'message' => 'Shift marked as completed successfully.',
//             'data' => [
//                 'shift_id' => $shift->id,
//                 'candidate_completed' => true,
//                 'candidate_completed_at' => $shift->candidate_completed_at,
//             ]
//         ]);

//     } catch (ValidationException $e) {

//         return response()->json([
//             'success'=>false,
//             'message'=>'Validation Error',
//             'errors'=>$e->errors()
//         ],422);

//     } catch (\Throwable $e) {

//         return response()->json([
//             'success'=>false,
//             'message'=>$e->getMessage()
//         ],500);
//     }
// }

// public function completedFillinShifts(Request $request)
// {
//     try {

//         $candidate = $request->user();

//         $responses = FillinShiftResponse::where('candidate_id', $candidate->id)
//             ->whereHas('shift', function ($q) use ($candidate) {
//                 $q->where('status', 'completed')
//                     ->where('booked_candidate_id', $candidate->id);
//             })
//             ->with([
//                 'shift.specialization',
//                 'shift.clinic.recruiter',
//             ])
//             ->orderByDesc('responded_at')
//             ->get();

//         $data = $responses->map(function ($response) {

//             $shift = $response->shift;

//             if (!$shift) {
//                 return null;
//             }

//             $clinic = $shift->clinic;

//             return [

//                 'shift_id' => $shift->id,
//                 'title' => $shift->title,

//                 'specialization' => optional(
//                     $shift->specialization
//                 )->name,

//                 'hourly_rate' => number_format(
//                     (float) $shift->hourly_rate,
//                     2,
//                     '.',
//                     ''
//                 ),

//                 'shift_date' => $shift->shift_date,
//                 'start_time' => $shift->start_time,
//                 'end_time' => $shift->end_time,

//                 'address' => $shift->address,
//                 'short_address' => $shift->short_address,
//                 'city' => $shift->city,

//                 'status' => $shift->status,

//                 'candidate_completed' => (bool) $shift->candidate_completed,
//                 'candidate_completed_at' => $shift->candidate_completed_at,
//                 'completed_at' => $shift->completed_at,

//                 'clinic' => [
//                     'id' => $clinic->id ?? null,
//                     'practice_name' => $clinic->practice_name ?? $clinic->name ?? null,
//                     'profile' => !empty($clinic->profile)
//                         ? config('filepaths.clinic.public_url') . $clinic->profile
//                         : null,
//                     'address' => $clinic->address ?? null,
//                     'phone' => $clinic->phone ?? null,
//                     'recruiter_name' => $clinic->recruiter->name ?? null,
//                     'recruiter_email' => $clinic->recruiter->email ?? null,
//                 ],

//                 'created_at' => $shift->created_at,

//             ];
//         })->filter()->values();

//         return response()->json([
//             'success' => true,
//             'message' => 'Completed fill-in shifts fetched successfully.',
//             'total' => $data->count(),
//             'data' => $data,
//         ], 200);

//     } catch (\Throwable $e) {

//         \Log::error('Completed Candidate Fill-in Shifts Error', [
//             'message' => $e->getMessage(),
//             'file' => $e->getFile(),
//             'line' => $e->getLine(),
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Something went wrong.',
//         ], 500);
//     }
// }

// =================================================

public function requestCancellation(Request $request)
{
    try {

        $validator = Validator::make($request->all(), [
            'shift_id'   => 'required|exists:fillin_shifts,id',
            'reason'     => 'required|string|max:500',
            'notes'      => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $candidate = $request->user();

        DB::beginTransaction();

        $shift = FillinShift::where('id', $request->shift_id)
            ->lockForUpdate()
            ->first();

        if (!$shift) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Shift not found.',
            ]);
        }

        // Only booked candidate can request cancellation
        if (
            $shift->status !== 'confirmed' ||
            (int)$shift->booked_candidate_id !== (int)$candidate->id
        ) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Only accepted bookings can request cancellation.',
            ]);
        }

        // Prevent duplicate pending request
        $alreadyPending = FillinShiftCancellationRequest::where(
                'fillin_shift_id',
                $shift->id
            )
            ->where('candidate_id', $candidate->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Cancellation request already submitted.',
            ]);
        }

        $attachment = null;

        if ($request->hasFile('attachment')) {

            $file = $request->file('attachment');

            $filename = time() . '_' . uniqid() . '.' .
                $file->getClientOriginalExtension();

            $file->move(
                public_path('uploads/cancellation'),
                $filename
            );

            $attachment = 'uploads/cancellation/' . $filename;
        }

        $cancellation = FillinShiftCancellationRequest::create([

            'fillin_shift_id' => $shift->id,

            'candidate_id' => $candidate->id,

            'clinic_id' => $shift->clinic_id,

            'reason' => $request->reason,

            'notes' => $request->notes,

            'attachment' => $attachment,

            'status' => 'pending',

        ]);

        $recruiter = Recruiter::find($shift->clinic->recruiter_id ?? null);
        $candidateProfile = $candidate->profile ? $candidate->profile : '';

        if ($recruiter) {
            $recruiter->notify(new \App\Notifications\RecruiterFCMNotification(
                $candidate->name . ' requested to cancel ' . $shift->title,
                'Cancellation request',
                null,
                $candidateProfile,
                'cancellation_request',
                $cancellation->id,
                'candidate'
            ));

            foreach ($recruiter->fcmTokens as $fcm) {
                $this->fcm->sendToToken(
                    $fcm->fcm_token,
                    'Cancellation request',
                    $candidate->name . ' requested to cancel ' . $shift->title,
                    'cancellation_request',
                    $cancellation->id,
                    $candidateProfile,
                    'candidate'
                );
            }
        }

        DB::commit();

        return response()->json([

            'status' => true,

            'message' => 'Cancellation request submitted successfully.',

            'data' => $cancellation,

        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([

            'status' => false,

            'message' => $e->getMessage(),

            'line' => $e->getLine(),

        ], 500);
    }
}


public function myCancellationRequests(Request $request)
{
    try {

        $candidate = $request->user();

        $query = FillinShiftCancellationRequest::with([
            'shift.specialization',
            'clinic.recruiter'
        ])
        ->where('candidate_id', $candidate->id);

        $status = $request->get('status', 'all');

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $requests = $query->latest()->get();

        $data = $requests->map(function ($item) {

            return [

                'request_id' => $item->id,

                'status' => $item->status,

                'reason' => $item->reason,

                'notes' => $item->notes,

                'attachment' => $item->attachment
                    ? asset($item->attachment)
                    : null,

                'recruiter_remark' => $item->recruiter_remark,

                'approved_at' => $item->approved_at,

                'shift' => [

                    'id' => optional($item->shift)->id,

                    'title' => optional($item->shift)->title,

                    'specialization' => optional(optional($item->shift)->specialization)->name,

                    'hourly_rate' => optional($item->shift)->hourly_rate,

                    'shift_date' => optional($item->shift)->shift_date,

                    'start_time' => optional($item->shift)->start_time,

                    'end_time' => optional($item->shift)->end_time,

                    'status' => optional($item->shift)->status,

                ],

                'clinic' => [

                    'id' => optional($item->clinic)->id,

                    'name' => optional($item->clinic)->name,

                    'address' => optional($item->clinic)->address,

                ],

                'created_at' => $item->created_at,

            ];

        });

        return response()->json([
            'status' => true,
            'message' => 'Cancellation requests fetched successfully.',
            'total' => $data->count(),
            'data' => $data,
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ],500);
    }
}


public function cancellationRequestDetail(Request $request, $id)
{
    try {

        $candidate = $request->user();

        $cancellation = FillinShiftCancellationRequest::with([
            'shift.specialization',
            'clinic.recruiter'
        ])
        ->where('candidate_id', $candidate->id)
        ->find($id);

        if (!$cancellation) {

            return response()->json([
                'status' => false,
                'message' => 'Cancellation request not found.',
            ],404);
        }

        return response()->json([

            'status' => true,

            'message' => 'Cancellation request detail fetched successfully.',

            'data' => [

                'request_id' => $cancellation->id,

                'status' => $cancellation->status,

                'reason' => $cancellation->reason,

                'notes' => $cancellation->notes,

                'attachment' => $cancellation->attachment
                    ? asset($cancellation->attachment)
                    : null,

                'recruiter_remark' => $cancellation->recruiter_remark,

                'approved_at' => $cancellation->approved_at,

                'shift' => [

                    'id' => optional($cancellation->shift)->id,

                    'title' => optional($cancellation->shift)->title,

                    'specialization' => optional(optional($cancellation->shift)->specialization)->name,

                    'hourly_rate' => optional($cancellation->shift)->hourly_rate,

                    'shift_date' => optional($cancellation->shift)->shift_date,

                    'start_time' => optional($cancellation->shift)->start_time,

                    'end_time' => optional($cancellation->shift)->end_time,

                    'address' => optional($cancellation->shift)->address,

                    'status' => optional($cancellation->shift)->status,

                ],

                'clinic' => [

                    'id' => optional($cancellation->clinic)->id,

                    'name' => optional($cancellation->clinic)->name,

                    'address' => optional($cancellation->clinic)->address,

                    'phone' => optional($cancellation->clinic)->phone,

                    'email' => optional(optional($cancellation->clinic)->recruiter)->email,

                ],

                'created_at' => $cancellation->created_at,

            ]

        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ],500);
    }
}



}

