<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Requests\Recruiter\{CreateJobRequest, StausJobRequest, scheduleInterViewRequest};
use App\Models\{JobListing, Candidate, ScheduleInterview, Recruiter};
use App\Http\Traits\RestResponse;
use App\Http\Resources\Recruiter\{JobListResource, RecruiterResource};
use App\Http\Resources\{CandidatesResource, ScheduleInterviewResource, CandidateListResource, InterviewListResource};
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\{FillinShift, FillinShiftResponse, FillinShiftCancellationRequest, Software};
use App\Http\Resources\Recruiter\SearchCandidateResource;
use Illuminate\Validation\ValidationException;
  use App\Http\Requests\Recruiter\SearchCandidateRequest;
  use App\Models\Branch;
  
use App\Http\Requests\Recruiter\CheckAvailabilityRequest;


class RecruiterJobController extends Controller
{
    use RestResponse;

    public $clinicId = null;
    protected $fcm;

    public function __construct()
    {
        $this->fcm = new FirebaseNotificationService();

        if (auth()->check()) {
            $this->clinicId = auth()->user()->clinic->id ?? null;
        }
    }

    /**
     * jobList
     * Developer Faizan khan
     * @param  mixed $request
     * @return void
     */
    public function jobList(Request $request)
    {
        try {
            $data = JobListing::with('clinic', 'specialization', 'employmentTypes')
                ->where('clinic_id', $request->user()->clinic->id)->withCount('candidates')
                ->orderByDesc('id')->get();
            if (count($data) > 0) {
                return $this->recordFoundWithResponse(JobListResource::collection($data));
            } else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * viewjob
     * Developer Faizan khan
     * @param  mixed $id
     * @return void
     */
    public function viewjob($id)
    {
        try {
            $data = JobListing::with('clinic', 'specialization', 'employmentTypes')
                ->withCount('candidates')->find($id);
            if ($data)
                return $this->recordFoundWithResponse(new JobListResource($data));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    /**
     * createJobs
     *
     * @param  mixed $request
     * @return void
     */
    

public function createJobs(CreateJobRequest $request)
    {
        try {

            /*
        |--------------------------------------------------------------------------
        | PREPARE JOB DATA
        |--------------------------------------------------------------------------
        */

            $requestData = $request->requestData();

            /*
        |--------------------------------------------------------------------------
        | UPLOAD IMAGE
        |--------------------------------------------------------------------------
        */

            if ($request->hasFile('image')) {

                $image = $request->file('image');

                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $image->move(public_path('uploads/jobs'), $imageName);

                $requestData['image'] = 'uploads/jobs/' . $imageName;
            }

            /*
        |--------------------------------------------------------------------------
        | CREATE OR UPDATE JOB
        |--------------------------------------------------------------------------
        */

            if ($request->id) {

                $data = JobListing::find($request->id);

                if (!$data) {
                    return $this->recordNotFoundResponse();
                }

                /*
            |--------------------------------------------------------------------------
            | DELETE OLD IMAGE IF NEW IMAGE UPLOADED
            |--------------------------------------------------------------------------
            */

                if ($request->hasFile('image') && $data->image && file_exists(public_path($data->image))) {
                    unlink(public_path($data->image));
                }

                $data->update($requestData);
            } else {

                $data = JobListing::create($requestData);
            }

            /*
        |--------------------------------------------------------------------------
        | SYNC SOFTWARE
        |--------------------------------------------------------------------------
        */

            if ($data) {

                $data->softwareList()->sync($request->software ?? []);
                $data->branches()->sync($request->branch_ids ?? []);

                /*
            |--------------------------------------------------------------------------
            | SEND NOTIFICATION ONLY ON CREATE
            |--------------------------------------------------------------------------
            */

                if (!$request->id) {

                    try {
                        $this->sendJobNotification($data);
                    } catch (\Exception $e) {
                        \Log::error('Notification failed: ' . $e->getMessage());
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | LOAD RELATIONS
            |--------------------------------------------------------------------------
            */

               $data->load([
                    'softwareList',
                    'specialization',
                    'clinic.recruiter',
                    'branches'
                ]);
                
                return response()->json([
                    'status' => true,
                    'message' => $request->id
                        ? 'Job Updated Successfully'
                        : 'Job Created Successfully',
                    'data' => new JobListResource($data)
                ], 200);
            }

            return $this->customErrorRes('Something went wrong. Please try again!');
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }



    /**
     * sendJobNotification
     *
     * @param  mixed $job
     * @return void
     */
    private function sendJobNotification($job)
    {
        $profession = $job->specialization ? $job->specialization->name : null;
        $software   = $job->softwareList ? $job->softwareList->pluck('id')->toArray() : [];
        $user       = auth()->guard('recruiter')->user();
        $icon       = $user->clinic && $user->clinic->profile ? $user->clinic->profile : '';
        $ids = Candidate::where(function ($query) use ($profession, $software, $job) {
            $query->when($profession, function ($q) use ($profession) {
                $q->orWhere('specialization_name', $profession);
            })
                ->orWhereHas('software_experiance', function ($q) use ($software) {
                    if (!empty($software)) {
                        $q->whereIn('software.id', $software);
                    }
                })
                ->orWhere('address', 'like', '%' . $job->city . '%')
                ->orWhere('year_of_experiance', 'like', '%' . $job->experiance_level . '%');
        })->pluck('id')->toArray();
        $urgent = $job->urgent ? '🚩Urgent' : '';
        $this->fcm->notifyCandidates(
            $ids,
            $job->title . ($urgent ? ' ' . $urgent . ' ' : ' ') . 'job has been created and matched your profile',
            'New Job Notification',
            '',
            $icon,
            'job created',
            $job->id,
            'recruiter'
        );
    }


    /**
     * delete
     *
     * @param  mixed $request
     * @return void
     */
    public function delete($id)
    {
        try {
            $data = JobListing::where('id', $id)->first();
            if ($data) {
                $data->delete();
                return $this->recordRemove($data);
            } else {
                return $this->recordNotFoundResponse();
            }
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * candidates who is applied our perticular jobs
     *
     * @param  mixed $id
     * @return void
     */
    public function jobCandidates($id)
    {
        try {
            $job = JobListing::find($id);

            if ($job) {
                $data = $job->candidates()->orderByDesc('id')->get();
                return $this->recordFoundWithResponse(CandidateListResource::collection($data)->each->withJobId($id));
            } else {
                return $this->recordNotFoundResponse();
            }
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * which all our jobs where single candidate applied
     *
     * @param  mixed $id
     * @return void
     */
    public function candidateAppliedJobs($id)
    {
        try {
            $candidate = Candidate::find($id);
            if ($candidate) {
                $data = $candidate->job()
                    ->where('clinic_id', auth()->user()->clinic->id)
                    ->withCount('candidates')
                    ->orderByDesc('id')
                    ->get();

                $resource = $data->map(function ($job) use ($id) {
                    return new JobListResource($job, $id);
                });

                return $this->recordFoundWithResponse($resource);
            } else {
                return $this->recordNotFoundResponse();
            }
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * all candidates where 
     *
     * @return void
     */
    public function allCandidates(Request $request)
    {
        try {
            $data = Candidate::ApiSearch($request)->whereStatus(1)->orderByDesc('id')->get();
            $rec = Recruiter::find(auth()->user()->id);
            $profile = ['profile' => $rec->clinic ? ($rec->clinic->profile ? config('filepaths.recruiter.public_url') . $rec->clinic->profile : null) : null,];
            if (count($data) > 0) {
                return $this->recordFoundResponseWithOtherFields(CandidateListResource::collection($data), $profile);
            } else {
                return $this->recordNotFoundResponse();
            }
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * viewCandidate
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return void
     */

    public function viewCandidate(Request $request, $id)
    {
        try {
            $candidate = Candidate::find($id);

            if (!$candidate)
                return $this->recordNotFoundResponse();

            $jobId = $request->job_id;
            $pivot = null;

            if ($jobId) {
                $job    = $candidate->job->where('id', $jobId)->first();
                $pivot  = $job ? $job->pivot : null;

                if ($pivot && count(explode(',', $pivot->status)) === 1) {
                    $this->sendViewNotification($candidate);
                    $this->updatePivotStatus($candidate, $jobId, 'Application Viewed');
                }
            }

            return $this->recordFoundWithResponse(new CandidatesResource($candidate));
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }


    private function sendViewNotification($data)
    {
        $user       = auth()->guard('recruiter')->user();
        $icon       = $user->clinic && $user->clinic->profile ? $user->clinic->profile : '';
        $this->fcm->notifyCandidates(
            $data->id,
            'Recruiter has viewed your application',
            'Update your application',
            '',
            $icon,
            'application viewed',
            $data->id,
            'recruiter'
        );
    }

    /**
     * updateJobStatus
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return void
     */
    public function updateJobStatus(StausJobRequest $request, $id)
    {
        try {
            $candidate = Candidate::find($id);
            if (!$candidate)
                return $this->recordNotFoundResponse();
            $this->sendStatusNotification($candidate, $request);
            $updated = $this->updatePivotStatus($candidate, $request->job_id, $request->status);

            return $updated ? $this->recordUpdate() : $this->customErrorRes('Something went wrong!');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    private function sendStatusNotification($data, $request)
    {
        $user       = auth()->guard('recruiter')->user();
        $icon       = $user->clinic && $user->clinic->profile ?  $user->clinic->profile : '';
        $this->fcm->notifyCandidates(
            $data->id,
            'Recruiter has' . $request->status . 'on your application',
            'Update your application',
            '',
            $icon,
            'update Status',
            $request->job_id,
            'recruiter'
        );
    }

    /**
     * scheduleInterView
     *
     * @param  mixed $request
     * @return void
     */
    public function scheduleInterView(scheduleInterViewRequest $request)
    {
        try {
            $id = ['id' => $request->id ?? null];
            $candidate = Candidate::find($request->candidate_id);

            $interview = ScheduleInterview::updateOrCreate($id, $request->requestData());
            if ($interview) {
                $this->updatePivotStatus($candidate, $request->job_id, 'Interview Scheduled ');
                $this->sendInterViewNotification($interview, $request);
                return $this->customSuccessRes("Interview Schedule Successfully.");
            }

            return $this->customErrorRes('Something went wrong!');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    private function sendInterViewNotification($interview, $request)
    {
        $user       = auth()->guard('recruiter')->user();
        $icon       = $user->clinic && $user->clinic->profile ? $user->clinic->profile : '';
        $this->fcm->notifyCandidates(
            $request->candidate_id,
            "You have received a new interview request from {$user->name}",
            "Interview Request",
            '',
            $icon,
            'interview scheduled',
            $interview->id,
            'recruiter'
        );
    }

    /**
     * completeInterview
     *
     * @param  mixed $id
     * @return void
     */

    public function completeInterview($id)
    {
        try {
            $interview = ScheduleInterview::find($id);
            if ($interview) {
                $interview->update(['status' => 1]);
                return $this->customSuccessRes("status update successfully");
            }
            return $this->customErrorRes('Something went wrong!');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * updatePivotStatus
     *
     * @param  mixed $candidate
     * @param  mixed $jobId
     * @param  mixed $newStatus
     * @return void
     */
    protected function updatePivotStatus($candidate, $jobId, $newStatus)
    {
        $existing = $candidate->job->where('id', $jobId)->first();
        if ($existing) {
            $statuses = explode(',', $existing->pivot->status);
            if (!in_array($newStatus, $statuses)) {
                $statuses[] = $newStatus;
                $candidate->job()->updateExistingPivot($jobId, [
                    'status' => implode(',', $statuses)
                ]);
            }
            return true;
        }
        return false;
    }

    /**
     * InterViewList
     *
     * @param  mixed $request
     * @return void
     */
    // public function InterViewList(Request $request)
    // {
    //     try {
    //         $data = ScheduleInterview::with('candidate', 'job', 'candidate_id', 'job_id', 'end_time')->orderByDesc('id')
    //             ->where('clinic_id', auth()->user()->clinic->id)->get();
    //         if (count($data) > 0)
    //             return $this->recordFoundWithResponse(InterviewListResource::collection($data));
    //         else
    //             return $this->recordNotFoundResponse();
    //     } catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }

    public function InterViewList(Request $request)
    {
        try {

            $data = ScheduleInterview::with([
                'candidate',
                'job'
            ])
                ->where('clinic_id', auth()->user()->clinic->id)
                ->orderByDesc('id')
                ->get();

            if ($data->count() > 0) {
                return $this->recordFoundWithResponse(
                    InterviewListResource::collection($data)
                );
            }

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


    public function recruiterCalendar(Request $request)
    {
        $request->validate([
            'recruiter_id' => 'required|integer',
            'month' => 'required|date_format:Y-m'
        ]);

        $recruiterId = $request->recruiter_id;
        $date = Carbon::createFromFormat('Y-m', $request->month);

        $data = Schedule::with('jobListing:id,title')
            ->where('recruiter_id', $recruiterId)
            ->whereMonth('interview_date', $date->month)
            ->whereYear('interview_date', $date->year)
            ->orderBy('interview_date')
            ->orderBy('interview_time')
            ->get();

        $grouped = $data->groupBy('interview_date')->map(function ($items) {
            return $items->map(function ($row) {
                return [
                    'time' => date("h:i A", strtotime($row->interview_time)),
                    'job_title' => optional($row->jobListing)->title,
                    'note' => $row->note
                ];
            });
        });

        return response()->json([
            'status' => true,
            'data' => $grouped
        ]);
    }


    public function rescheduleInterview(Request $request, $id)
    {
        try {

            // ✅ VALIDATION (loose + safe)
            $validator = \Validator::make($request->all(), [
                'candidate_id' => 'nullable|exists:candidates,id',
                'job_id'       => 'nullable|exists:job_listings,id',
                'date'         => 'required|date',
                'time'         => 'required',
                'end_time'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 0,
                    'status'     => 'failed',
                    'message'    => $validator->errors()->first()
                ]);
            }

            // ✅ FIND INTERVIEW
            $interview = ScheduleInterview::where('id', $id)
                ->where('clinic_id', auth()->user()->clinic->id)
                ->first();

            if (!$interview) {
                return $this->recordNotFoundResponse();
            }

            // ✅ UPDATE DATA (only if exists)
            $interview->update([
                'candidate_id' => $request->candidate_id ?? $interview->candidate_id,
                'job_id'       => $request->job_id ?? $interview->job_id,
                'title'        => $request->title ?? $interview->title,
                'date'         => $request->date,
                'time'         => $request->time,
                'end_time'     => $request->end_time,
                'link'         => $request->link ?? $interview->link,
                'notes'        => $request->notes ?? $interview->notes,
                'timezone'     => $request->timezone ?? config('app.timezone'),
                'status'       => 0
            ]);

            // ✅ FORMAT DATE
            $scheduledAt = Carbon::parse($interview->date . ' ' . $interview->time)->toIso8601String();

            // ✅ RESPONSE (frontend exact)
            return response()->json([
                'statusCode' => 200,
                'message'    => 'Interview rescheduled successfully',
                'data'       => [
                    'id'             => $interview->id,
                    'candidate_id'   => $interview->candidate_id,
                    'job_id'         => $interview->job_id,
                    'title'          => $interview->title,
                    'date'           => $interview->date,
                    'time'           => $interview->time,
                    'end_time'       => $interview->end_time,
                    'scheduled_at'   => $scheduledAt,
                    'timezone'       => $interview->timezone,
                    'link'           => $interview->link,
                    'notes'          => $interview->notes,
                    'status'         => 'Upcoming',
                    'updated_at'     => $interview->updated_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }




public function searchCandidates(SearchCandidateRequest $request)
{
    DB::beginTransaction();

    try {

        $recruiter = $request->user();

        $clinic = $recruiter->clinic;

        if (!$clinic) {

            return response()->json([
                'status' => false,
                'message' => 'Clinic not found.'
            ],404);
        }

        /*
        |--------------------------------------------------------------------------
        | Branch Ids
        |--------------------------------------------------------------------------
        */

        $branchIds = Branch::where('recruiter_id',$recruiter->id)
            ->whereIn('id',$request->branch_ids ?? [])
            ->pluck('id')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Software
        |--------------------------------------------------------------------------
        */

        $softwareIds = $request->software ?? [];

        $otherSoftware = Software::where('name','LIKE','%Other%')->first();

        $otherSoftwareValue = null;

        if ($otherSoftware &&
            in_array($otherSoftware->id,$softwareIds)) {

            if (!$request->filled('other_software')) {

                return response()->json([
                    'status'=>false,
                    'message'=>'Other software is required.'
                ],422);
            }

            $otherSoftwareValue = $request->other_software;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Shift
        |--------------------------------------------------------------------------
        */

        $shift = FillinShift::create([

            'clinic_id' => $clinic->id,

            'title' => $request->title,

            'specialization_id' => $request->specialization_id,

            'experiance_level' => $request->experiance_level,

            'software' => $softwareIds,

            'other_software' => $otherSoftwareValue,

            'vacancy' => $request->vacancy,

            'urgent' => $request->urgent ?? 0,

            'city' => $request->city,

            'address' => $request->address,

            'short_address' => $request->short_address,

            'job_description' => $request->job_description,

            'expire_date' => $request->expire_date,

            'latitude' => $request->latitude,

            'longitude' => $request->longitude,

            'hourly_rate' => $request->hourly_rate,

            'status' => 'pending',

            'branch_ids' => $branchIds,

            'shift_date' => $request->shift_date,

            'start_time' => date('H:i:s',strtotime($request->start_time)),

            'end_time' => date('H:i:s',strtotime($request->end_time)),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Candidates
        |--------------------------------------------------------------------------
        */

        $candidates = Candidate::with('specialization')
            ->where('status',1)
            ->whereHas('specialization',function($q) use($request){

                $q->where('id',$request->specialization_id);

            })
            ->latest()
            ->get();

        if($candidates->isNotEmpty()){

            $this->sendFillinShiftNotificationForCandidates(

                $shift,

                $candidates->pluck('id')->toArray(),

                'shift_created'

            );

        }

        DB::commit();

        return response()->json([

            'status'=>true,

            'message'=>'Candidates fetched successfully',

            'shift_id'=>$shift->id,

            'data'=>SearchCandidateResource::collection($candidates)

        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([

            'status'=>false,

            'message'=>$e->getMessage(),

            'line'=>$e->getLine(),

            'file'=>$e->getFile()

        ],500);

    }
}

    

public function checkAvailability(CheckAvailabilityRequest $request)
{
    try {

        $clinic = auth()->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'Clinic not found.',
            ], 404);
        }

        $shift = FillinShift::where('id', $request->shift_id)
            ->where('clinic_id', $clinic->id)
            ->first();

        if (!$shift) {
            return response()->json([
                'status'  => false,
                'message' => 'Shift not found.',
            ], 404);
        }

        $candidate = Candidate::where('id', $request->candidate_id)
            ->where('status', 1)
            ->first();

        if (!$candidate) {
            return response()->json([
                'status'  => false,
                'message' => 'Candidate not found or inactive.',
            ], 404);
        }

        $response = FillinShiftResponse::firstOrCreate(
            [
                'fillin_shift_id' => $shift->id,
                'candidate_id'    => $candidate->id,
            ],
            [
                'response'     => 'pending',
                'responded_at' => null,
            ]
        );

        if ($response->wasRecentlyCreated) {

            $this->sendFillinShiftNotificationForCandidates(
                $shift,
                [$candidate->id],
                'availability_request'
            );
        }

        return response()->json([
            'status'   => true,
            'message'  => $response->wasRecentlyCreated
                ? 'Availability request sent successfully.'
                : 'Availability request already sent.',
            'shift_id' => $shift->id,
            'data'     => $response,
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ], 500);
    }
}

    
    

public function availableResponses(Request $request, $shiftId)
{
    try {

        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'Clinic not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Shift
        |--------------------------------------------------------------------------
        */

        $shift = FillinShift::where([
                'id' => $shiftId,
                'clinic_id' => $clinic->id,
            ])
            ->first();

        if (!$shift) {
            return response()->json([
                'status'  => false,
                'message' => 'Shift not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Candidate Responses
        |--------------------------------------------------------------------------
        */

        $responses = FillinShiftResponse::with('candidate.specialization')
            ->where('fillin_shift_id', $shift->id)
            ->whereIn('response', ['available', 'not-available'])
            ->get();

        $isLocked = in_array($shift->status, ['confirmed', 'not-confirmed']);

        /*
        |--------------------------------------------------------------------------
        | Prepare Response
        |--------------------------------------------------------------------------
        */

        $data = $responses->map(function ($response) use ($request, $shift, $isLocked) {

            $candidate = (new SearchCandidateResource(
                $response->candidate
            ))->toArray($request);

            return [
                'response'      => $response->response,
                'responded_at'  => optional($response->responded_at)
                    ->format('Y-m-d H:i:s'),

                'candidate' => array_merge($candidate, [
                    'shift_date'    => $shift->shift_date,
                    'start_time'    => $shift->start_time,
                    'end_time'      => $shift->end_time,
                    'isConfirmShow' => !$isLocked,
                ]),
            ];
        });

        return response()->json([
            'status'         => true,
            'message'        => 'Candidates fetched successfully.',
            'shift_id'       => $shift->id,
            'total'          => $data->count(),
            'action_locked'  => $isLocked,
            'data'           => $data,
        ]);

    } catch (\Throwable $e) {

        Log::error('Available Responses Error', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
        ], 500);
    }
}
  


public function confirmBooking(Request $request)
{
    try {

        $validated = $request->validate([
            'shift_id'     => ['required', 'exists:fillin_shifts,id'],
            'candidate_id' => ['required', 'exists:candidates,id'],
            'status'       => ['required', 'in:confirmed,not-confirmed'],
        ]);

        $clinic = optional($request->user())->clinic;

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'Clinic not found.',
            ], 404);
        }

        $shift = FillinShift::with([
            'clinic',
            'specialization',
            'bookedCandidate.specialization',
        ])
        ->where('id', $validated['shift_id'])
        ->where('clinic_id', $clinic->id)
        ->first();

        if (!$shift) {
            return response()->json([
                'status' => false,
                'message' => 'Shift not found.',
            ], 404);
        }

        $availability = FillinShiftResponse::where([
            'fillin_shift_id' => $shift->id,
            'candidate_id'    => $validated['candidate_id'],
        ])->first();

        if (!$availability) {
            return response()->json([
                'status' => false,
                'message' => 'Availability request not found.',
            ], 404);
        }

        if (in_array($shift->status, ['confirmed', 'not-confirmed'])) {
            return response()->json([
                'status' => false,
                'message' => 'Action has already been taken for this shift.',
            ], 422);
        }

        if ($availability->response === 'expired') {
            return response()->json([
                'status' => false,
                'message' => 'Candidate request has expired.',
            ], 422);
        }

        if ($availability->response !== 'available') {
            return response()->json([
                'status' => false,
                'message' => 'Candidate is not available for this shift.',
            ], 422);
        }

        DB::transaction(function () use (
            &$shift,
            $validated
        ) {

            if ($validated['status'] === 'confirmed') {

                $shift->update([
                    'status'              => 'confirmed',
                    'booked_candidate_id' => $validated['candidate_id'],
                ]);

                FillinShiftResponse::where('fillin_shift_id', $shift->id)
                    ->where('candidate_id', '!=', $validated['candidate_id'])
                    ->whereIn('response', ['pending', 'available'])
                    ->update([
                        'response'   => 'expired',
                        'updated_at' => now(),
                    ]);

            } else {

                $shift->update([
                    'status'              => 'not-confirmed',
                    'booked_candidate_id' => null,
                ]);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        if ($validated['status'] === 'confirmed') {

            try {

                $this->sendFillinShiftNotificationForCandidates(
                    $shift,
                    [$validated['candidate_id']],
                    'booking_confirmed'
                );

            } catch (\Throwable $e) {

                Log::error('Booking Notification Failed', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $candidate = Candidate::with('specialization')
            ->find($validated['candidate_id']);

        $shift->refresh();

        return response()->json([
            'status'  => true,
            'message' => $validated['status'] == 'confirmed'
                ? 'Booking confirmed successfully.'
                : 'Candidate rejected successfully.',

            'data' => [
                'shift_id'            => $shift->id,
                'title'               => $shift->title,
                'status'              => $shift->status,
                'shift_date'          => $shift->shift_date,
                'start_time'          => $shift->start_time,
                'end_time'            => $shift->end_time,
                'hourly_rate'         => $shift->hourly_rate,
                'booked_candidate_id' => $shift->booked_candidate_id,
                'candidate' => $candidate
                    ? new SearchCandidateResource($candidate)
                    : null,
            ],
        ]);

    } catch (ValidationException $e) {

        return response()->json([
            'status' => false,
            'message' => 'Validation error.',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Throwable $e) {

        Log::error('Confirm Booking Error', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong.',
        ], 500);
    }
}


 public function myFillinBookings(Request $request)
{
    try {
        $clinic = $request->user()?->clinic;

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'Clinic not found for this recruiter',
            ], 404);
        }

        $status = $request->input('status', 'all');

        $query = FillinShift::query()
            ->where('clinic_id', $clinic->id)
            ->with([
                'specialization:id,name',
                'bookedCandidate',
                'bookedCandidate.specialization',
                'responses',
                'responses.candidate',
                'responses.candidate.specialization',
            ])
            ->withCount([
                'responses as available_count' => fn($q) => $q->where('response', 'available')
            ]);

        $query->when($status !== 'all', function ($q) use ($status) {

            if ($status === 'pending') {
                $q->whereNull('booked_candidate_id')
                    ->whereHas('responses', function ($r) {
                        $r->where('response', 'available');
                    });
            } else {
                $q->where('status', $status);
            }
        });

        $shifts = $query->latest()->get();

        $data = $shifts->map(function ($shift) {

            $candidate = $shift->bookedCandidate;

            if (!$candidate && $shift->status === 'not-confirmed') {
                $candidate = optional(
                    $shift->responses->firstWhere('candidate_id', '!=', null)
                )->candidate;
            }

            return [
                'shift_id' => $shift->id,
                'title' => $shift->title,
                'specialization' => $shift->specialization?->name,
                'hourly_rate' => number_format((float) $shift->hourly_rate, 2, '.', ''),
                'address' => $shift->address,
                'short_address' => $shift->short_address,
                'city' => $shift->city,
                'expire_date' => $shift->expire_date,
                'status' => $this->getRecruiterFillinStatus($shift),
                'available_count' => $shift->available_count,

                'candidate' => $candidate ? [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'profile' => $candidate->profile
                        ? config('filepaths.candidate.public_url') . $candidate->profile
                        : null,
                    'phone' => $candidate->phone,
                    'specialization' => $candidate->specialization?->name,
                    'year_of_experiance' => $candidate->year_of_experiance,
                    'rating' => $candidate->rating ?? 0,
                    'review_count' => $candidate->review_count ?? 0,
                ] : null,

                'created_at' => $shift->created_at,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Fill-in bookings fetched successfully',
            'data' => $data,
        ], 200);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong.',
        ], 500);
    }
}
    private function getRecruiterFillinStatus($shift)
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

        if (($shift->available_count ?? 0) > 0 && !$shift->booked_candidate_id) {
            return 'not-confirmed';
        }

        return $shift->status;
    }




    private function sendFillinShiftNotificationForCandidates($shift, array $candidateIds, string $type = 'shift_created')
    {
        try {
            if (empty($candidateIds)) {
                return;
            }

            $user = auth()->guard('recruiter')->user();
            $icon = '';

            if ($user && $user->clinic && $user->clinic->profile) {
                $icon = $user->clinic->profile;
            }

            $urgent = $shift->urgent ? ' 🚩Urgent' : '';

            if ($type === 'availability_request') {
                $title = 'New Availability Request';
                $body = $shift->title . $urgent . ' availability request received';
                $notificationType = 'availability request';
            } elseif ($type === 'booking_confirmed') {
                $title = 'Booking Confirmed';
                $body = $shift->title . ' booking has been confirmed';
                $notificationType = 'booking confirmed';
            } else {
                $title = 'New Fill-In Shift';
                $body = $shift->title . $urgent . ' shift has been created and matched your profile';
                $notificationType = 'fillin shift created';
            }

            $this->fcm->notifyCandidates(
                $candidateIds,
                $body,
                $title,
                '',
                $icon,
                $notificationType,
                $shift->id,
                'recruiter'
            );
        } catch (\Exception $e) {
            Log::error('Fillin shift notification failed: ' . $e->getMessage());
        }
    }

    private function sendCancellationStatusNotification($shift, $candidateId, string $status)
{
    try {

        $candidate = Candidate::find($candidateId);

        if (!$candidate) {
            return;
        }

        $user = auth()->guard('recruiter')->user();

        $icon = '';

        if ($user && $user->clinic && $user->clinic->profile) {
            $icon = $user->clinic->profile;
        }

        if ($status === 'approved') {

            $title = 'Cancellation Approved';

            $body = 'Your cancellation request for shift "' .
                $shift->title .
                '" has been approved.';

            $notificationType = 'cancellation approved';

        } else {

            $title = 'Cancellation Rejected';

            $body = 'Your cancellation request for shift "' .
                $shift->title .
                '" has been rejected.';

            $notificationType = 'cancellation rejected';
        }

        $candidate = Candidate::find($candidateId);

        if ($candidate) {
            $candidate->notify(new \App\Notifications\CandidateFCMNotification(
                $body,
                $title,
                null,
                $icon,
                $notificationType,
                $shift->id,
                'recruiter'
            ));

            foreach ($candidate->fcmTokens as $fcm) {
                $this->fcm->sendToToken(
                    $fcm->fcm_token,
                    $title,
                    $body,
                    $notificationType,
                    $shift->id,
                    $icon,
                    'recruiter'
                );
            }
        }

    } catch (\Exception $e) {

        Log::error(
            'Cancellation status notification failed: ' .
            $e->getMessage()
        );
    }
}

public function calendar(Request $request)
{
    $request->validate([
        'from' => ['required', 'date'],
        'to'   => ['required', 'date', 'after_or_equal:from'],
    ]);

    try {

        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'Clinic not found.',
            ], 404);
        }

        $shifts = FillinShift::query()
            ->with([
                'bookedCandidate:id,name',
            ])
            ->withCount([
                'responses as available_count' => function ($query) {
                    $query->where('response', 'available');
                }
            ])
            ->where('clinic_id', $clinic->id)
            ->whereBetween('shift_date', [
                $request->from,
                $request->to,
            ])
            ->orderBy('shift_date')
            ->get();

        $data = $shifts->map(function ($shift) {

            if ($shift->status === 'confirmed') {
                $status = 'confirmed';

            } elseif ($shift->available_count > 0) {
                $status = 'pending';

            } elseif (
                $shift->expire_date &&
                now()->gt($shift->expire_date)
            ) {
                $status = 'expired';

            } else {
                $status = 'not-confirmed';
            }

            return [

                'id' => $shift->id,

                'title' => $shift->title,

                'date' => optional($shift->shift_date)->format('Y-m-d'),

                'start_time' => date('H:i', strtotime($shift->start_time)),

                'end_time' => date('H:i', strtotime($shift->end_time)),

                'status' => $status,

                'vacancy' => $shift->vacancy,

                'available_count' => $shift->available_count,

                'candidate_id' => optional($shift->bookedCandidate)->id,

                'candidate_name' => optional($shift->bookedCandidate)->name,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Calendar fetched successfully.',
            'data'    => $data,
        ]);

    } catch (\Throwable $e) {

        \Log::error('Calendar Error', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
        ], 500);
    }
}
    
   


// =============================================================


public function cancellationRequests(Request $request)
{
    try {

        $clinic = $request->user()?->clinic;

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'Clinic not found for this recruiter.',
            ], 404);
        }

        $status = $request->input('status', 'pending');

        $requests = FillinShiftCancellationRequest::query()
            ->where('clinic_id', $clinic->id)
            ->with([
                'shift.specialization:id,name',
                'candidate',
                'candidate.specialization',
            ])
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->get();

        $data = $requests->map(function ($item) {

            $shift = $item->shift;
            $candidate = $item->candidate;

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
                    'address' => $shift?->address,
                    'status' => $shift?->status,
                ],

                'candidate' => [
                    'id' => $candidate?->id,
                    'name' => $candidate?->name,
                    'email' => $candidate?->email,
                    'phone' => $candidate?->phone,
                    'profile' => !empty($candidate?->profile)
                        ? config('filepaths.candidate.public_url') . $candidate->profile
                        : null,
                    'specialization' => $candidate?->specialization?->name,
                    'year_of_experiance' => $candidate?->year_of_experiance,
                ],

                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Cancellation requests fetched successfully.',
            'total' => $data->count(),
            'data' => $data,
        ], 200);

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

        $clinic = $request->user()?->clinic;

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'Clinic not found for this recruiter.',
            ], 404);
        }

        $cancellation = FillinShiftCancellationRequest::query()
            ->where('clinic_id', $clinic->id)
            ->with([
                'shift.specialization:id,name',
                'candidate',
                'candidate.specialization',
            ])
            ->find($id);

        if (!$cancellation) {
            return response()->json([
                'status' => false,
                'message' => 'Cancellation request not found.',
            ], 404);
        }

        $shift = $cancellation->shift;
        $candidate = $cancellation->candidate;

        return response()->json([
            'status' => true,
            'message' => 'Cancellation request detail fetched successfully.',
            'data' => [

                'request_id' => $cancellation->id,
                'status' => $cancellation->status,
                'reason' => $cancellation->reason,
                'notes' => $cancellation->notes,

                'attachment' => !empty($cancellation->attachment)
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

                'candidate' => [
                    'id' => $candidate?->id,
                    'name' => $candidate?->name,
                    'email' => $candidate?->email,
                    'phone' => $candidate?->phone,

                    'profile' => !empty($candidate?->profile)
                        ? config('filepaths.candidate.public_url') . $candidate->profile
                        : null,

                    'specialization' => $candidate?->specialization?->name,
                    'year_of_experiance' => $candidate?->year_of_experiance,
                ],

                'created_at' => $cancellation->created_at,
            ],
        ], 200);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong.',
        ], 500);
    }
}



public function updateCancellationStatus(Request $request)
{
    $request->validate([
        'request_id'       => 'required|exists:fillin_shift_cancellation_requests,id',
        'status'           => 'required|in:approved,rejected',
        'recruiter_remark' => 'nullable|string|max:1000',
    ]);

    try {

        $clinic = $request->user()?->clinic;

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'Clinic not found.',
            ], 404);
        }

        $result = DB::transaction(function () use ($request, $clinic) {

            $cancelRequest = FillinShiftCancellationRequest::where('id', $request->request_id)
                ->where('clinic_id', $clinic->id)
                ->lockForUpdate()
                ->first();

            if (!$cancelRequest) {
                throw new \Exception('Cancellation request not found.');
            }

            if ($cancelRequest->status !== 'pending') {
                throw new \Exception('This request has already been processed.');
            }

            $shift = FillinShift::lockForUpdate()->find($cancelRequest->fillin_shift_id);

            if (!$shift) {
                throw new \Exception('Shift not found.');
            }

            $cancelRequest->update([
                'status' => $request->status,
                'recruiter_remark' => $request->recruiter_remark,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            if ($request->status === 'approved') {

                $shift->update([
                    'status' => 'pending',
                    'booked_candidate_id' => null,
                ]);

                FillinShiftResponse::where([
                    'fillin_shift_id' => $shift->id,
                    'candidate_id' => $cancelRequest->candidate_id,
                ])->update([
                    'response' => 'cancelled',
                    'responded_at' => now(),
                ]);

                FillinShiftResponse::where('fillin_shift_id', $shift->id)
                    ->where('candidate_id', '!=', $cancelRequest->candidate_id)
                    ->where('response', 'expired')
                    ->update([
                        'response' => 'available',
                        'responded_at' => now(),
                    ]);

            } else {

                $shift->update([
                    'status' => 'confirmed',
                    'booked_candidate_id' => $cancelRequest->candidate_id,
                ]);

                FillinShiftResponse::where([
                    'fillin_shift_id' => $shift->id,
                    'candidate_id' => $cancelRequest->candidate_id,
                ])->update([
                    'response' => 'confirmed',
                    'responded_at' => now(),
                ]);
            }

            return compact('cancelRequest', 'shift');
        });

        try {
            $this->sendCancellationStatusNotification(
                $result['shift'],
                $result['cancelRequest']->candidate_id,
                $request->status
            );
        } catch (\Throwable $e) {
            Log::error('Cancellation notification failed', [
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => $request->status === 'approved'
                ? 'Cancellation request approved successfully.'
                : 'Cancellation request rejected successfully.',
            'data' => [
                'request_id' => $result['cancelRequest']->id,
                'request_status' => $result['cancelRequest']->status,
                'shift_id' => $result['shift']->id,
                'shift_status' => $result['shift']->status,
                'booked_candidate_id' => $result['shift']->booked_candidate_id,
            ],
        ]);

    } catch (\Throwable $e) {

        Log::error('Update Cancellation Status Error', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'status' => false,
            'message' => config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong.',
        ], 500);
    }
}

}
