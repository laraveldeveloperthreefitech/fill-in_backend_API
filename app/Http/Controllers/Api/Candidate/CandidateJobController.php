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

use App\Http\Resources\Candidate\ShiftDetailResource;
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

    

public function shiftDetail(Request $request, $shiftId)
{
    try {

        $candidate = auth()->guard('candidate')->user();

        $shift = FillinShift::with([
                'clinic.recruiter',
                'specialization'
            ])
            ->find($shiftId);

        if (!$shift) {

            return response()->json([
                'success' => false,
                'message' => 'Shift not found.',
            ], 404);
        }

        $response = FillinShiftResponse::where([
                'fillin_shift_id' => $shift->id,
                'candidate_id'    => $candidate->id,
            ])->first();

        if (!$response) {

            return response()->json([
                'success' => false,
                'message' => 'Shift request not found.',
            ], 404);
        }

        $shift->candidate_id = $candidate->id;
        $shift->response = $response->response;
        $shift->responded_at = optional($response->responded_at)?->format('Y-m-d');

        $shift->is_my_booking =
            $shift->status === 'confirmed'
            && $shift->booked_candidate_id == $candidate->id;

        return response()->json([

            'success' => true,

            'message' => 'Shift detail fetched successfully.',

            'data' => new ShiftDetailResource($shift)

        ]);

    } catch (\Throwable $e) {

        return response()->json([

            'success' => false,

            'message' => $e->getMessage(),

            'line' => $e->getLine(),

            'file' => $e->getFile(),

        ], 500);
    }
}



public function respondAvailability(Request $request)
{
    try {

        $validated = $request->validate([
            'shift_id' => ['required', 'exists:fillin_shifts,id'],
            'response' => ['required', 'in:available,not-available'],
        ]);

        $candidate = $request->user();

        if (!$candidate) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated candidate',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Shift
        |--------------------------------------------------------------------------
        */

        $shift = FillinShift::with([
            'clinic',
            'specialization',
        ])->find($validated['shift_id']);

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Candidate Response
        |--------------------------------------------------------------------------
        */

        $shiftResponse = FillinShiftResponse::where([
            'fillin_shift_id' => $shift->id,
            'candidate_id'    => $candidate->id,
        ])->first();

        if (!$shiftResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Shift request not found for this candidate',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Already Responded
        |--------------------------------------------------------------------------
        */

        if (in_array($shiftResponse->response, ['available', 'not-available'])) {

            return response()->json([
                'success' => false,
                'message' => 'You have already responded to this shift.',
                'data' => [
                    'shift_id'         => $shift->id,
                    'candidate_id'     => $candidate->id,
                    'current_response' => $shiftResponse->response,
                    'responded_at'     => optional($shiftResponse->responded_at)
                        ->format('Y-m-d H:i:s'),
                ]
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Save Response
        |--------------------------------------------------------------------------
        */

        $shiftResponse->update([
            'response'     => $validated['response'],
            'responded_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Send Notification
        |--------------------------------------------------------------------------
        */

        try {

            $this->sendAvailabilityNotification(
                $shift,
                $candidate,
                $validated['response']
            );

        } catch (\Throwable $e) {

            Log::error('Availability Notification Failed', [
                'message' => $e->getMessage(),
            ]);
        }

        $shiftResponse->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Response submitted successfully.',
            'data' => [
                'shift_id'     => $shift->id,
                'title'        => $shift->title,
                'candidate_id' => $candidate->id,
                'response'     => $shiftResponse->response,
                'responded_at' => optional($shiftResponse->responded_at)
                    ->format('Y-m-d H:i:s'),
            ]
        ]);

    } catch (ValidationException $e) {

        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Throwable $e) {

        Log::error('Respond Availability Error', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
        ], 500);
    }
}

  public function myFillinBookings(Request $request)
{
    try {

        $candidate = $request->user();
        $status = $request->input('status', 'all');

        $query = FillinShiftResponse::query()
            ->where('candidate_id', $candidate->id)
            ->with([
                'shift.specialization:id,name',
                'shift.clinic',
                'shift.clinic.recruiter',
            ]);

        $query->when($status !== 'all', function ($q) use ($status, $candidate) {

            if ($status === 'confirmed') {

                $q->whereHas('shift', function ($shift) use ($candidate) {
                    $shift->where('status', 'confirmed')
                        ->where('booked_candidate_id', $candidate->id);
                });

            } else {

                $q->where('response', $status);
            }
        });

        $responses = $query
            ->join(
                'fillin_shifts',
                'fillin_shift_responses.fillin_shift_id',
                '=',
                'fillin_shifts.id'
            )
            ->latest('fillin_shifts.created_at')
            ->select('fillin_shift_responses.*')
            ->get();

        $data = $responses->map(function ($response) use ($candidate) {

            $shift = $response->shift;

            if (!$shift) {
                return null;
            }

            $clinic = $shift->clinic;
            $recruiter = $clinic?->recruiter;

            return [
                'shift_id' => $shift->id,
                'title' => $shift->title,
                'specialization' => $shift->specialization?->name,
                'hourly_rate' => number_format((float) $shift->hourly_rate, 2, '.', ''),
                'address' => $shift->address,
                'short_address' => $shift->short_address,
                'city' => $shift->city,
                'expire_date' => $shift->expire_date,

                'my_response' => $response->response ?? 'pending',
                'my_response_at' => $response->responded_at,

                'shift_status' => $this->getCandidateShiftStatus($shift),

                'is_my_booking' => (
                    $shift->status === 'confirmed'
                    && $shift->booked_candidate_id == $candidate->id
                ),

                'recruiter' => [
                    'id' => $clinic?->recruiter_id,
                    'clinic_id' => $clinic?->id,
                    'practice_name' => $clinic?->practice_name ?? $clinic?->name,
                    'profile' => !empty($clinic?->profile)
                        ? config('filepaths.clinic.public_url') . $clinic->profile
                        : null,
                    'description' => $clinic?->description,
                    'address' => $clinic?->address,
                    'postcode' => $clinic?->postcode,
                    'phone' => $clinic?->phone,
                    'web_link' => $clinic?->web_link,
                    'established_year' => $clinic?->established_year,
                    'practice_size' => $clinic?->practice_size,
                    'working_hours' => $clinic?->working_hours,
                    'verification' => $clinic?->verification,
                    'status' => $clinic?->status,

                    'recruiter_name' => $recruiter?->name,
                    'recruiter_email' => $recruiter?->email,
                    'recruiter_profile' => !empty($recruiter?->profile)
                        ? config('filepaths.recruiter.public_url') . $recruiter->profile
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
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong.',
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
        'from' => ['required', 'date'],
        'to'   => ['required', 'date', 'after_or_equal:from'],
    ]);

    try {

        $candidate = $request->user();

        $responses = FillinShiftResponse::query()
            ->where('candidate_id', $candidate->id)
            ->whereHas('shift', function ($q) use ($request) {
                $q->whereBetween('shift_date', [
                    $request->from,
                    $request->to,
                ]);
            })
            ->with([
                'shift.clinic.recruiter',
            ])
            ->get();

        $data = $responses->map(function ($response) {

            $shift = $response->shift;

            if (!$shift) {
                return null;
            }

            $status = match (true) {
                $shift->status === 'confirmed'
                    && $shift->booked_candidate_id == $response->candidate_id => 'confirmed',

                $response->response === 'available'     => 'accepted',
                $response->response === 'not-available' => 'declined',

                default => 'requested',
            };

            return [
                'id' => $shift->id,
                'title' => $shift->title,
                'practice_name' => $shift->clinic?->name,
                'recruiter_id' => $shift->clinic?->recruiter?->id,
                'date' => $shift->shift_date?->format('Y-m-d') ?? date('Y-m-d', strtotime($shift->shift_date)),
                'start_time' => date('H:i', strtotime($shift->start_time)),
                'end_time' => date('H:i', strtotime($shift->end_time)),
                'status' => $status,
            ];
        })->filter()->values();

        return response()->json([
            'status' => true,
            'message' => 'Calendar fetched successfully.',
            'data' => $data,
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong.',
        ], 500);
    }
}
    


// =================================================

public function requestCancellation(Request $request)
{
    $request->validate([
        'shift_id'   => 'required|exists:fillin_shifts,id',
        'reason'     => 'required|string|max:500',
        'notes'      => 'nullable|string',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
    ]);

    try {

        $candidate = $request->user();

        $cancellation = DB::transaction(function () use ($request, $candidate) {

            $shift = FillinShift::with('clinic.recruiter')
                ->lockForUpdate()
                ->find($request->shift_id);

            if (!$shift) {
                throw new \Exception('Shift not found.');
            }

            if (
                $shift->status !== 'confirmed' ||
                $shift->booked_candidate_id != $candidate->id
            ) {
                throw new \Exception('Only confirmed bookings can request cancellation.');
            }

            $alreadyPending = FillinShiftCancellationRequest::where([
                'fillin_shift_id' => $shift->id,
                'candidate_id'    => $candidate->id,
                'status'          => 'pending',
            ])->exists();

            if ($alreadyPending) {
                throw new \Exception('Cancellation request already submitted.');
            }

            $attachment = null;

            if ($request->hasFile('attachment')) {

                $file = $request->file('attachment');

                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('uploads/cancellation'), $filename);

                $attachment = 'uploads/cancellation/' . $filename;
            }

            $cancellation = FillinShiftCancellationRequest::create([
                'fillin_shift_id' => $shift->id,
                'candidate_id'    => $candidate->id,
                'clinic_id'       => $shift->clinic_id,
                'reason'          => $request->reason,
                'notes'           => $request->notes,
                'attachment'      => $attachment,
                'status'          => 'pending',
            ]);

            $recruiter = $shift->clinic?->recruiter;

            if ($recruiter) {

                $candidateProfile = $candidate->profile ?? '';

                $title = 'Cancellation request';
                $body = "{$candidate->name} requested to cancel {$shift->title}";

                $recruiter->notify(
                    new \App\Notifications\RecruiterFCMNotification(
                        $body,
                        $title,
                        null,
                        $candidateProfile,
                        'cancellation_request',
                        $cancellation->id,
                        'candidate'
                    )
                );

                foreach ($recruiter->fcmTokens as $token) {

                    $this->fcm->sendToToken(
                        $token->fcm_token,
                        $title,
                        $body,
                        'cancellation_request',
                        $cancellation->id,
                        $candidateProfile,
                        'candidate'
                    );
                }
            }

            return $cancellation;
        });

        return response()->json([
            'status' => true,
            'message' => 'Cancellation request submitted successfully.',
            'data' => $cancellation,
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong.',
        ], 500);
    }
}


public function myCancellationRequests(Request $request)
{
    try {

        $candidate = $request->user();
        $status = $request->input('status', 'all');

        $requests = FillinShiftCancellationRequest::query()
            ->where('candidate_id', $candidate->id)
            ->with([
                'shift.specialization:id,name',
                'clinic:id,name,address',
            ])
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->get();

        $data = $requests->map(function ($item) {

            $shift = $item->shift;
            $clinic = $item->clinic;

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
                    'id' => $shift?->id,
                    'title' => $shift?->title,
                    'specialization' => $shift?->specialization?->name,
                    'hourly_rate' => $shift?->hourly_rate,
                    'shift_date' => $shift?->shift_date,
                    'start_time' => $shift?->start_time,
                    'end_time' => $shift?->end_time,
                    'status' => $shift?->status,
                ],

                'clinic' => [
                    'id' => $clinic?->id,
                    'name' => $clinic?->name,
                    'address' => $clinic?->address,
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
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong.',
        ], 500);
    }
}


public function cancellationRequestDetail(Request $request, $id)
{
    try {

        $candidate = $request->user();

        $cancellation = FillinShiftCancellationRequest::query()
            ->where('candidate_id', $candidate->id)
            ->with([
                'shift.specialization:id,name',
                'clinic.recruiter:id,email',
            ])
            ->find($id);

        if (!$cancellation) {
            return response()->json([
                'status' => false,
                'message' => 'Cancellation request not found.',
            ], 404);
        }

        $shift = $cancellation->shift;
        $clinic = $cancellation->clinic;
        $recruiter = $clinic?->recruiter;

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
                    'id' => $shift?->id,
                    'title' => $shift?->title,
                    'specialization' => $shift?->specialization?->name,
                    'hourly_rate' => $shift?->hourly_rate,
                    'shift_date' => $shift?->shift_date,
                    'start_time' => $shift?->start_time,
                    'end_time' => $shift?->end_time,
                    'address' => $shift?->address,
                    'status' => $shift?->status,
                ],

                'clinic' => [
                    'id' => $clinic?->id,
                    'name' => $clinic?->name,
                    'address' => $clinic?->address,
                    'phone' => $clinic?->phone,
                    'email' => $recruiter?->email,
                ],

                'created_at' => $cancellation->created_at,
            ],
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong.',
        ], 500);
    }
}


}

