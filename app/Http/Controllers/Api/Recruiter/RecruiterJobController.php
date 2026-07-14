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
use App\Models\{FillinShift, FillinShiftResponse, FillinShiftCancellationRequest, Software};
use App\Http\Resources\Recruiter\SearchCandidateResource;
use Illuminate\Validation\ValidationException;


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
    // public function createJobs(CreateJobRequest $request){
    //     try{
    //         $id   = ['id' => $request->id ? $request->id : null];
    //         $data = JobListing::updateOrCreate($id,$request->requestData());
    //         if($data){
    //             $data->softwareList()->sync($request->software);
    //             // $data->employmentTypes()->sync($request->employment_type);
    //             // $data->requireDocuments()->sync($request->require_document);
    //             // $data->specialization()->sync($request->profession);
    //         if(!$request->id){
    //         	 $this->sendJobNotification($data);
    //         }


    //             return $this->newRecordSaveResponse($data);
    //         }  
    //         else
    //             return $this->customErrorRes('Somthing went wrong.Please try again!');
    //     }catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }

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



    public function searchCandidates(Request $request)
    {
        try {
            $request->validate([
                'title'             => 'required|string|max:255',
                'specialization_id' => 'required|exists:specializations,id',
                'experiance_level'  => 'nullable|string|max:255',

                'software'          => 'nullable|array',
                'software.*'        => 'integer|exists:software,id',
                'other_software'    => 'nullable|string|max:255',

                'vacancy'           => 'nullable|integer',
                'urgent'            => 'nullable',
                'city'              => 'nullable|string|max:255',
                'address'           => 'nullable|string',
                'short_address'     => 'nullable|string|max:255',
                'job_description'   => 'nullable|string',
                'expire_date'       => 'nullable|date',
                'latitude'          => 'nullable|numeric',
                'longitude'         => 'nullable|numeric',
                'hourly_rate'       => 'required|numeric',
                'shift_date'        => 'required|date',
                'start_time'        => 'required',
                'end_time'          => 'required',
                'branch_ids'        => 'nullable|array',
                'branch_ids.*'      => 'nullable|integer',
            ]);

            $clinic = $request->user()->clinic;

            if (!$clinic) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Clinic not found for this recruiter',
                ], 404);
            }

            $branchIds = collect($request->branch_ids ?? [])
                ->filter(fn ($id) => !empty($id))
                ->values()
                ->all();

            if (!empty($branchIds)) {
                $existingBranchIds = \App\Models\Branch::where('recruiter_id', $request->user()->id)
                    ->whereIn('id', $branchIds)
                    ->pluck('id')
                    ->toArray();

                $branchIds = $existingBranchIds;
            }

            $softwareIds = $request->software ?? [];

            /**
             * Find "Other" software option from database.
             * Your table has: Other (Please Specify )
             */
            $otherSoftware = Software::where('name', 'LIKE', '%Other%')->first();

            $otherSoftwareId = $otherSoftware?->id;

            /**
             * If user selected Other software, then other_software is required.
             */
            if ($otherSoftwareId && in_array($otherSoftwareId, $softwareIds) && !$request->filled('other_software')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => [
                        'other_software' => [
                            'The other software field is required when Other software is selected.',
                        ],
                    ],
                ], 422);
            }

            /**
             * If Other software is not selected, do not save other_software.
             */
            $otherSoftwareValue = null;

            if ($otherSoftwareId && in_array($otherSoftwareId, $softwareIds)) {
                $otherSoftwareValue = $request->other_software;
            }

            $shift = FillinShift::create([
                'clinic_id'         => $clinic->id,
                'title'             => $request->title,
                'specialization_id' => $request->specialization_id,
                'experiance_level'  => $request->experiance_level,
                'software'          => $softwareIds,
                'other_software'    => $otherSoftwareValue,
                'vacancy'           => $request->vacancy,
                'urgent'            => $request->urgent ?? 0,
                'city'              => $request->city,
                'address'           => $request->address,
                'short_address'     => $request->short_address,
                'job_description'   => $request->job_description,
                'expire_date'       => $request->expire_date,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'hourly_rate'       => $request->hourly_rate,
                'status'            => 'pending',
                'branch_ids' => $branchIds,
                'shift_date'        => $request->shift_date,
                'start_time'        => date('H:i:s', strtotime($request->start_time)),
                'end_time'          => date('H:i:s', strtotime($request->end_time)),
            ]);

            $candidates = Candidate::where('status', 1)
                ->whereHas('specialization', function ($query) use ($request) {
                    $query->where('specializations.id', $request->specialization_id);
                })
                ->with('specialization')
                ->orderByDesc('id')
                ->get();

            $candidateIds = $candidates->pluck('id')->toArray();

            if (!empty($candidateIds)) {
                $this->sendFillinShiftNotificationForCandidates(
                    $shift,
                    $candidateIds,
                    'shift_created'
                );
            }

            return response()->json([
                'status'   => true,
                'message'  => 'Candidates fetched successfully',
                'shift_id' => $shift->id,
                'data'     => SearchCandidateResource::collection($candidates),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }

    // public function checkAvailability(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'shift_id'     => 'required|exists:fillin_shifts,id',
    //             'candidate_id' => 'required|exists:candidates,id',
    //         ]);

    //         $clinic = $request->user()->clinic ?? null;

    //         if (!$clinic) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Clinic not found for this recruiter',
    //             ], 404);
    //         }

    //         $shift = FillinShift::where('id', $request->shift_id)
    //             ->where('clinic_id', $clinic->id)
    //             ->first();

    //         if (!$shift) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Shift not found for this clinic',
    //             ], 404);
    //         }

    //         $candidate = Candidate::where('id', $request->candidate_id)
    //             ->where('status', 1)
    //             ->first();

    //         if (!$candidate) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Candidate not found or inactive',
    //             ], 404);
    //         }

    //         $response = FillinShiftResponse::updateOrCreate(
    //             [
    //                 'fillin_shift_id' => $shift->id,
    //                 'candidate_id'    => $candidate->id,
    //             ],
    //             [
    //                 'response'     => 'available',
    //                 'responded_at' => null,
    //             ]
    //         );

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SEND AVAILABILITY REQUEST NOTIFICATION
    //     |--------------------------------------------------------------------------
    //     */

    //         $this->sendFillinShiftNotification(
    //             $shift,
    //             [$candidate->id],
    //             'availability_request'
    //         );

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Availability request sent successfully',
    //             'shift_id' => $shift->id,
    //             'data' => $response,
    //         ], 200);
    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validation error',
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //         ], 500);
    //     }
    // }
    
    public function checkAvailability(Request $request)
{
    try {
        $request->validate([
            'shift_id'     => 'required|exists:fillin_shifts,id',
            'candidate_id' => 'required|exists:candidates,id',
        ]);

        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'Clinic not found for this recruiter',
            ], 404);
        }

        $shift = FillinShift::where('id', $request->shift_id)
            ->where('clinic_id', $clinic->id)
            ->first();

        if (!$shift) {
            return response()->json([
                'status'  => false,
                'message' => 'Shift not found for this clinic',
            ], 404);
        }

        $candidate = Candidate::where('id', $request->candidate_id)
            ->where('status', 1)
            ->first();

        if (!$candidate) {
            return response()->json([
                'status'  => false,
                'message' => 'Candidate not found or inactive',
            ], 404);
        }

        // Check existing request
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

        // Send notification only if new request created
        if ($response->wasRecentlyCreated) {

            $this->sendFillinShiftNotification(
                $shift,
                [$candidate->id],
                'availability_request'
            );

            $message = 'Availability request sent successfully';
        } else {

            $message = 'Availability request already sent';
        }

        return response()->json([
            'status'   => true,
            'message'  => $message,
            'shift_id' => $shift->id,
            'data'     => $response,
        ], 200);

    } catch (ValidationException $e) {

        return response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Throwable $e) {

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ], 500);
    }
}

    // public function availableResponses(Request $request, $shiftId)
    // {
    //     try {
    //         $clinic = $request->user()->clinic ?? null;

    //         if (!$clinic) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Clinic not found for this recruiter',
    //             ], 404);
    //         }

    //         $shift = FillinShift::where('id', $shiftId)
    //             ->where('clinic_id', $clinic->id)
    //             ->first();

    //         if (!$shift) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Shift not found',
    //             ], 404);
    //         }

    //         $responses = FillinShiftResponse::where('fillin_shift_id', $shift->id)
    //             ->where('response', 'available')
    //             ->with(['candidate.specialization'])
    //             ->get()
    //             ->pluck('candidate')
    //             ->filter()
    //             ->values();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Available responses fetched successfully',
    //             'data' => SearchCandidateResource::collection($responses),
    //         ], 200);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //         ], 500);
    //     }
    // }
    

public function availableResponses(Request $request, $shiftId)
{
    try {

        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'Clinic not found for this recruiter',
            ], 404);
        }

        $shift = FillinShift::where('id', $shiftId)
            ->where('clinic_id', $clinic->id)
            ->first();

        if (!$shift) {
            return response()->json([
                'status' => false,
                'message' => 'Shift not found',
            ], 404);
        }

        $responses = FillinShiftResponse::with([
            'candidate.specialization'
        ])
        ->where('fillin_shift_id', $shift->id)
        ->whereIn('response', ['available', 'not-available'])
        ->get();

        $data = $responses->map(function ($item) use ($shift, $request) {

            $candidate = (new SearchCandidateResource($item->candidate))
                ->toArray($request);

            $candidate['shift_date'] = $shift->shift_date;
            $candidate['start_time'] = $shift->start_time;
            $candidate['end_time']   = $shift->end_time;
            $candidate['isConfirmShow'] = in_array($shift->status, ['confirmed', 'not-confirmed']) ? 0 : 1;

            return [
                'response'     => $item->response,
                'responded_at' => $item->responded_at,
                'candidate'    => $candidate,
            ];
        });

        return response()->json([
            'status'   => true,
            'message'  => 'Candidates fetched successfully',
            'shift_id' => $shift->id,
            'total'    => $data->count(),
            'data'     => $data,
            'action_locked' => in_array(
    $shift->status,
    ['confirmed','not-confirmed']
),
        ], 200);

    } catch (\Throwable $e) {

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
        ], 500);
    }
}

  

// public function confirmBooking(Request $request)
// {
//     try {

//         $request->validate([
//             'shift_id'     => 'required|exists:fillin_shifts,id',
//             'candidate_id' => 'required|exists:candidates,id',
//             'status'       => 'required|in:confirm,not_confirm',
//         ]);

//         $clinic = $request->user()->clinic;

//         if (!$clinic) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Clinic not found for this recruiter',
//             ], 404);
//         }

//         $shift = FillinShift::where('id', $request->shift_id)
//             ->where('clinic_id', $clinic->id)
//             ->first();

//         if (!$shift) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Shift not found',
//             ], 404);
//         }

//         $availability = FillinShiftResponse::where('fillin_shift_id', $shift->id)
//             ->where('candidate_id', $request->candidate_id)
//             ->first();

//         if (!$availability) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Availability request not found',
//             ], 404);
//         }

//         DB::beginTransaction();

//         // ==========================================
//         // CONFIRM BOOKING
//         // ==========================================
//         if ($request->status == 'confirm') {

//             if ($availability->response !== 'available') {
//                 return response()->json([
//                     'status' => false,
//                     'message' => 'Candidate has not accepted this shift',
//                 ], 422);
//             }

//             // Book selected candidate
//             $shift->update([
//                 'status'              => 'booked',
//                 'booked_candidate_id' => $request->candidate_id,
//             ]);

//             // Release all other candidates
//             FillinShiftResponse::where('fillin_shift_id', $shift->id)
//                 ->where('candidate_id', '!=', $request->candidate_id)
//                 ->whereIn('response', ['pending', 'available'])
//                 ->update([
//                     'response'   => 'released',
//                     'updated_at' => now(),
//                 ]);

//             DB::commit();

//             // Refresh relations
//             $shift->refresh()->load([
//                 'clinic',
//                 'specialization',
//                 'bookedCandidate.specialization',
//             ]);

//             // Booking confirmed notification to selected candidate
//             try {

//                 $this->sendFillinShiftNotification(
//                     $shift,
//                     [$request->candidate_id],
//                     'booking_confirmed'
//                 );

//             } catch (\Exception $e) {

//                 Log::error(
//                     'Booking confirmation notification failed: ' .
//                     $e->getMessage()
//                 );
//             }

//             // Released candidates IDs
//             $releasedCandidateIds = FillinShiftResponse::where(
//                     'fillin_shift_id',
//                     $shift->id
//                 )
//                 ->where('response', 'released')
//                 ->pluck('candidate_id')
//                 ->toArray();

//             // Release notification to other candidates
//             if (!empty($releasedCandidateIds)) {

//                 try {

//                     $this->sendFillinShiftNotification(
//                         $shift,
//                         $releasedCandidateIds,
//                         'shift_released'
//                     );

//                 } catch (\Exception $e) {

//                     Log::error(
//                         'Release notification failed: ' .
//                         $e->getMessage()
//                     );
//                 }
//             }

//             $message = 'Booking confirmed successfully';
//         }

//         // ==========================================
//         // NOT CONFIRM
//         // ==========================================
//         if ($request->status == 'not_confirm') {

//             $shift->update([
//                 'status'              => 'open',
//                 'booked_candidate_id' => null,
//             ]);

//             // Reject selected candidate
//             FillinShiftResponse::where('fillin_shift_id', $shift->id)
//                 ->where('candidate_id', $request->candidate_id)
//                 ->update([
//                     'response'   => 'released',
//                     'updated_at' => now(),
//                 ]);

//             DB::commit();

//             $message = 'Candidate rejected successfully';
//         }

//         $shift->refresh()->load([
//             'clinic',
//             'specialization',
//             'bookedCandidate.specialization',
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => $message,
//             'data'    => [
//                 'shift_id'            => $shift->id,
//                 'title'               => $shift->title,
//                 'status'              => $shift->status,
//                 'shift_date'          => $shift->shift_date,
//                 'start_time'          => $shift->start_time,
//                 'end_time'            => $shift->end_time,
//                 'hourly_rate'         => $shift->hourly_rate,
//                 'booked_candidate_id' => $shift->booked_candidate_id,
//             'candidate' => $shift->bookedCandidate
//                 ? new SearchCandidateResource($shift->bookedCandidate)
//                 : null,
//             ],
//         ], 200);

//     } catch (ValidationException $e) {

//         return response()->json([
//             'status'  => false,
//             'message' => 'Validation error',
//             'errors'  => $e->errors(),
//         ], 422);

//     } catch (\Throwable $e) {

//         DB::rollBack();

//         return response()->json([
//             'status'  => false,
//             'message' => $e->getMessage(),
//             'line'    => $e->getLine(),
//             'file'    => $e->getFile(),
//         ], 500);
//     }
// }

public function confirmBooking(Request $request)
{
    try {

        $request->validate([
            'shift_id'     => 'required|exists:fillin_shifts,id',
            'candidate_id' => 'required|exists:candidates,id',
            'status'       => 'required|in:confirmed,not-confirmed',
        ]);

        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'Clinic not found for this recruiter',
            ]);
        }

        $shift = FillinShift::where('id', $request->shift_id)
            ->where('clinic_id', $clinic->id)
            ->first();

        if (!$shift) {
            return response()->json([
                'status'  => false,
                'message' => 'Shift not found',
            ]);
        }

        $availability = FillinShiftResponse::where('fillin_shift_id', $shift->id)
            ->where('candidate_id', $request->candidate_id)
            ->first();

        if (!$availability) {
            return response()->json([
                'status'  => false,
                'message' => 'Availability request not found',
            ]);
        }

        DB::beginTransaction();

        // Prevent multiple actions on the same shift
        if (in_array($shift->status, ['confirmed', 'not-confirmed'])) {
        
            DB::rollBack();
        
            return response()->json([
                'status'  => false,
                'message' => 'Action has already been taken for this shift.',
            ]);
        }

        if ($availability->response === 'expired') {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Candidate has already been expired',
            ]);
        }

        // ==========================================
        // CONFIRMED
        // ==========================================
        if ($request->status === 'confirmed') {

            if ($availability->response !== 'available') {

                DB::rollBack();

                return response()->json([
                    'status'  => false,
                    'message' => 'Candidate has not accepted this shift',
                ]);
            }

            $shift->update([
                'status'              => 'confirmed',
                'booked_candidate_id' => $request->candidate_id,
            ]);

            FillinShiftResponse::where('fillin_shift_id', $shift->id)
                ->where('candidate_id', '!=', $request->candidate_id)
                ->whereIn('response', ['pending', 'available'])
                ->update([
                    'response'   => 'expired',
                    'updated_at' => now(),
                ]);

            DB::commit();

            try {
                $this->sendFillinShiftNotification(
                    $shift,
                    [$request->candidate_id],
                    'booking_confirmed'
                );
            } catch (\Exception $e) {
                Log::error(
                    'Booking confirmation notification failed: ' .
                    $e->getMessage()
                );
            }

            $shift->refresh()->load([
                'clinic',
                'specialization',
                'bookedCandidate.specialization',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Booking confirmed successfully',
                'data'    => [
                    'shift_id'            => $shift->id,
                    'title'               => $shift->title,
                    'status'              => $shift->status,
                    'shift_date'          => $shift->shift_date,
                    'start_time'          => $shift->start_time,
                    'end_time'            => $shift->end_time,
                    'hourly_rate'         => $shift->hourly_rate,
                    'booked_candidate_id' => $shift->booked_candidate_id,
                    'candidate' => $shift->bookedCandidate
                        ? new SearchCandidateResource($shift->bookedCandidate)
                        : null,
                ],
            ], 200);
        }

        // ==========================================
        // NOT CONFIRMED
        // ==========================================
        if ($request->status === 'not-confirmed') {

            if ($availability->response !== 'available') {

                DB::rollBack();

                return response()->json([
                    'status'  => false,
                    'message' => 'Candidate is not available',
                ]);
            }

            $shift->update([
                'status'              => 'not-confirmed',
                'booked_candidate_id' => null,
            ]);

            DB::commit();

            $candidate = Candidate::with('specialization')
                ->find($request->candidate_id);

            return response()->json([
                'status'  => true,
                'message' => 'Candidate rejected successfully',
                'data'    => [
                    'shift_id'            => $shift->id,
                    'title'               => $shift->title,
                    'status'              => $shift->status,
                    'shift_date'          => $shift->shift_date,
                    'start_time'          => $shift->start_time,
                    'end_time'            => $shift->end_time,
                    'hourly_rate'         => $shift->hourly_rate,
                    'booked_candidate_id' => null,
                    'candidate_id'        => $request->candidate_id,
                    'candidate' => $candidate
                        ? new SearchCandidateResource($candidate)
                        : null,
                ],
            ], 200);
        }

        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => 'Invalid status provided',
        ]);

    } catch (ValidationException $e) {

        return response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $e->errors(),
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Confirm Booking Error', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage(),
        ]);
    }
}


  public function myFillinBookings(Request $request)
{
try {
$clinic = $request->user()->clinic ?? null;


    if (!$clinic) {
        return response()->json([
            'status' => false,
            'message' => 'Clinic not found for this recruiter',
        ], 404);
    }

    $query = FillinShift::query()
        ->where('clinic_id', $clinic->id)
        ->with([
            'specialization',
            'bookedCandidate.specialization',
            'responses.candidate.specialization',
        ])
        ->withCount([
            'responses as available_count' => function ($q) {
                $q->where('response', 'available');
            }
        ]);

    $status = $request->get('status', 'all');

    if ($status !== 'all') {
        if ($status === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('booked_candidate_id')
                    ->whereHas('responses', function ($r) {
                        $r->where('response', 'available');
                    });
            });
        } else {
            $query->where('status', $status);
        }
    }

    $shifts = $query
        ->orderByDesc('created_at')
        ->get();

    $data = $shifts->map(function ($shift) {

        // Confirmed candidate
        $candidate = $shift->bookedCandidate;

        // Not-confirmed candidate fallback
        if (!$candidate && $shift->status === 'not-confirmed') {
            $response = $shift->responses
                ->where('candidate_id', '!=', null)
                ->first();

            $candidate = $response?->candidate;
        }

        return [
            'shift_id' => $shift->id,
            'title' => $shift->title,
            'specialization' => optional($shift->specialization)->name,
            'hourly_rate' => number_format((float) $shift->hourly_rate, 2, '.', ''),
            'address' => $shift->address,
            'short_address' => $shift->short_address,
            'city' => $shift->city,
            'expire_date' => $shift->expire_date,
            'status' => $this->getRecruiterFillinStatus($shift),
            'available_count' => $shift->available_count ?? 0,

            'candidate' => $candidate ? [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'profile' => $candidate->profile
                    ? config('filepaths.candidate.public_url') . $candidate->profile
                    : null,
                'phone' => $candidate->phone,
                'specialization' => optional($candidate->specialization)->name,
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
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
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



    // private function sendFillinShiftNotification($shift, array $candidateIds, string $type = 'shift_created')
    // {
    //     try {
    //         if (empty($candidateIds)) {
    //             return;
    //         }

    //         $user = auth()->guard('recruiter')->user();

    //         $icon = '';

    //         if ($user && $user->clinic && $user->clinic->profile) {
    //             $icon = $user->clinic->profile;
    //         }

    //         $urgent = $shift->urgent ? ' 🚩Urgent' : '';

    //         if ($type === 'availability_request') {
    //             $title = 'New Availability Request';
    //             $body = $shift->title . $urgent . ' availability request received';
    //             $notificationType = 'availability request';
    //         } elseif ($type === 'booking_confirmed') {
    //             $title = 'Booking Confirmed';
    //             $body = $shift->title . ' booking has been confirmed';
    //             $notificationType = 'booking confirmed';
    //         } else {
    //             $title = 'New Fill-In Shift';
    //             $body = $shift->title . $urgent . ' shift has been created and matched your profile';
    //             $notificationType = 'fillin shift created';
    //         }

    //         $this->fcm->notifyCandidates(
    //             $candidateIds,
    //             $body,
    //             $title,
    //             '',
    //             $icon,
    //             $notificationType,
    //             $shift->id,
    //             'recruiter'
    //         );
    //     } catch (\Exception $e) {
    //         Log::error('Fillin shift notification failed: ' . $e->getMessage());
    //     }
    // }

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


    //added by sr.
    public function calendar(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date',
        ]);

        $clinic = $request->user()->clinic;

        $shifts = FillinShift::with([
            'bookedCandidate'
        ])
            ->where('clinic_id', $clinic->id)
            ->whereBetween('shift_date', [
                $request->from,
                $request->to
            ])
            ->get();

        $data = $shifts->map(function ($shift) {

            $availableCount = FillinShiftResponse::where(
                'fillin_shift_id',
                $shift->id
            )
                ->where('response', 'available')
                ->count();

            if ($shift->status === 'confirmed') {
                $status = 'confirmed';
            } elseif ($availableCount > 0) {
                $status = 'pending';
            } elseif (
                $shift->expire_date &&
                now()->toDateString() > $shift->expire_date
            ) {
                $status = 'expired';
            } else {
                $status = 'not-confirmed';
            }

            return [
                'id' => $shift->id,
                'title' => $shift->title,
                // 'date' => $shift->shift_date,
                 'date' => $shift->shift_date->format('Y-m-d'),
                'start_time' => date('H:i', strtotime($shift->start_time)),
                'end_time' => date('H:i', strtotime($shift->end_time)),
                'status' => $status,
                'vacancy' => $shift->vacancy,
                'available_count' => $availableCount,
                'candidate_id' => optional($shift->bookedCandidate)->id,
                'candidate_name' => optional($shift->bookedCandidate)->name,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Calendar fetched',
            'data' => $data
        ]);
    }
    

    
    
   


// =============================================================


public function cancellationRequests(Request $request)
{
    try {

        $clinic = $request->user()->clinic ?? null;

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'Clinic not found for this recruiter',
            ], 404);
        }

        $query = FillinShiftCancellationRequest::with([
            'shift.specialization',
            'candidate.specialization',
        ])
        ->where('clinic_id', $clinic->id);

        // status = pending / approved / rejected / all
        $status = $request->get('status', 'pending');

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $requests = $query
            ->latest()
            ->get();

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

                /*
                |--------------------------------------------------------------------------
                | Shift Details
                |--------------------------------------------------------------------------
                */

                'shift' => [

                    'id' => optional($item->shift)->id,

                    'title' => optional($item->shift)->title,

                    'specialization' => optional(optional($item->shift)->specialization)->name,

                    'hourly_rate' => optional($item->shift)->hourly_rate,

                    'shift_date' => optional($item->shift)->shift_date,

                    'start_time' => optional($item->shift)->start_time,

                    'end_time' => optional($item->shift)->end_time,

                    'address' => optional($item->shift)->address,

                    'status' => optional($item->shift)->status,
                ],

                /*
                |--------------------------------------------------------------------------
                | Candidate Details
                |--------------------------------------------------------------------------
                */

                'candidate' => [

                    'id' => optional($item->candidate)->id,

                    'name' => optional($item->candidate)->name,

                    'email' => optional($item->candidate)->email,

                    'phone' => optional($item->candidate)->phone,

                    'profile' => optional($item->candidate)->profile
                        ? config('filepaths.candidate.public_url') . $item->candidate->profile
                        : null,

                    'specialization' => optional(optional($item->candidate)->specialization)->name,

                    'year_of_experiance' => optional($item->candidate)->year_of_experiance,

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
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ], 500);
    }
}



public function cancellationRequestDetail(Request $request, $id)
{
    try {

        $clinic = $request->user()->clinic ?? null;

        if (!$clinic) {
            return response()->json([
                'status' => false,
                'message' => 'Clinic not found for this recruiter',
            ], 404);
        }

        $cancellation = FillinShiftCancellationRequest::with([
            'shift.specialization',
            'candidate.specialization',
        ])
        ->where('clinic_id', $clinic->id)
        ->find($id);

        if (!$cancellation) {
            return response()->json([
                'status' => false,
                'message' => 'Cancellation request not found.',
            ], 404);
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

                /*
                |--------------------------------------------------------------------------
                | Shift Details
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Candidate Details
                |--------------------------------------------------------------------------
                */

                'candidate' => [

                    'id' => optional($cancellation->candidate)->id,

                    'name' => optional($cancellation->candidate)->name,

                    'email' => optional($cancellation->candidate)->email,

                    'phone' => optional($cancellation->candidate)->phone,

                    'profile' => optional($cancellation->candidate)->profile
                        ? config('filepaths.candidate.public_url') . $cancellation->candidate->profile
                        : null,

                    'specialization' => optional(optional($cancellation->candidate)->specialization)->name,

                    'year_of_experiance' => optional($cancellation->candidate)->year_of_experiance,

                ],

                'created_at' => $cancellation->created_at,

            ],
        ], 200);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ], 500);
    }
}

// public function updateCancellationStatus(Request $request)
// {
//     try {

//         $validator = Validator::make($request->all(), [
//             'request_id'        => 'required|exists:fillin_shift_cancellation_requests,id',
//             'status'            => 'required|in:approved,rejected',
//             'recruiter_remark'  => 'nullable|string|max:1000',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => $validator->errors()->first(),
//             ], 422);
//         }

//         $clinic = $request->user()->clinic;

//         if (!$clinic) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Clinic not found.',
//             ], 404);
//         }

//         DB::beginTransaction();

//         $cancelRequest = FillinShiftCancellationRequest::where('id', $request->request_id)
//             ->where('clinic_id', $clinic->id)
//             ->lockForUpdate()
//             ->first();

//         if (!$cancelRequest) {

//             DB::rollBack();

//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Cancellation request not found.',
//             ]);
//         }

//         if ($cancelRequest->status != 'pending') {

//             DB::rollBack();

//             return response()->json([
//                 'status'  => false,
//                 'message' => 'This request has already been processed.',
//             ]);
//         }

//         $shift = FillinShift::where('id', $cancelRequest->fillin_shift_id)
//             ->lockForUpdate()
//             ->first();

//         if (!$shift) {

//             DB::rollBack();

//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Shift not found.',
//             ]);
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Update Cancellation Request
//         |--------------------------------------------------------------------------
//         */

//         $cancelRequest->update([
//             'status'            => $request->status,
//             'recruiter_remark'  => $request->recruiter_remark,
//             'approved_by'       => $request->user()->id,
//             'approved_at'       => now(),
//         ]);

//         /*
//         |--------------------------------------------------------------------------
//         | APPROVED
//         |--------------------------------------------------------------------------
//         */

//         if ($request->status == 'approved') {

//             // Make shift available again
//             $shift->update([
//                 'status'              => 'pending',
//                 'booked_candidate_id' => null,
//             ]);

//             // Candidate who cancelled
//             FillinShiftResponse::where('fillin_shift_id', $shift->id)
//                 ->where('candidate_id', $cancelRequest->candidate_id)
//                 ->update([
//                     'response'     => 'cancelled',
//                     'responded_at' => now(),
//                 ]);

//             // Other candidates can respond again
//             FillinShiftResponse::where('fillin_shift_id', $shift->id)
//                 ->where('candidate_id', '!=', $cancelRequest->candidate_id)
//                 ->where('response', 'expired')
//                 ->update([
//                     'response'     => 'available', // or pending
//                     'responded_at' => now(),
//                 ]);

//             /*
//             |--------------------------------------------------------------------------
//             | TODO Notification
//             |--------------------------------------------------------------------------
//             */

//             // $this->sendFillinShiftNotification(...);

//         }

//         /*
//         |--------------------------------------------------------------------------
//         | REJECTED
//         |--------------------------------------------------------------------------
//         */

//         else {

//             $shift->update([
//                 'status'              => 'confirmed',
//                 'booked_candidate_id' => $cancelRequest->candidate_id,
//             ]);

//             FillinShiftResponse::where('fillin_shift_id', $shift->id)
//                 ->where('candidate_id', $cancelRequest->candidate_id)
//                 ->update([
//                     'response'     => 'confirmed',
//                     'responded_at' => now(),
//                 ]);

//             /*
//             |--------------------------------------------------------------------------
//             | TODO Notification
//             |--------------------------------------------------------------------------
//             */

//             // $this->sendFillinShiftNotification(...);

//         }

//         DB::commit();

//         return response()->json([
//             'status'  => true,
//             'message' => $request->status == 'approved'
//                 ? 'Cancellation request approved successfully.'
//                 : 'Cancellation request rejected successfully.',
//             'data' => [
//                 'request_id'         => $cancelRequest->id,
//                 'request_status'     => $cancelRequest->status,
//                 'shift_id'           => $shift->id,
//                 'shift_status'       => $shift->status,
//                 'booked_candidate_id'=> $shift->booked_candidate_id,
//             ]
//         ], 200);

//     } catch (\Throwable $e) {

//         DB::rollBack();

//         Log::error('Update Cancellation Status Error',[
//             'message' => $e->getMessage(),
//             'line'    => $e->getLine(),
//             'file'    => $e->getFile(),
//         ]);

//         return response()->json([
//             'status'  => false,
//             'message' => 'Something went wrong.',
//             'error'   => $e->getMessage(),
//         ],500);
//     }
// }

public function updateCancellationStatus(Request $request)
{
    try {

        $validator = Validator::make($request->all(), [
            'request_id'       => 'required|exists:fillin_shift_cancellation_requests,id',
            'status'           => 'required|in:approved,rejected',
            'recruiter_remark' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'status'  => false,
                'message' => 'Clinic not found.',
            ], 404);
        }

        DB::beginTransaction();

        $cancelRequest = FillinShiftCancellationRequest::where('id', $request->request_id)
            ->where('clinic_id', $clinic->id)
            ->lockForUpdate()
            ->first();

        if (!$cancelRequest) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Cancellation request not found.',
            ]);
        }

        if ($cancelRequest->status != 'pending') {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'This request has already been processed.',
            ]);
        }

        $shift = FillinShift::where('id', $cancelRequest->fillin_shift_id)
            ->lockForUpdate()
            ->first();

        if (!$shift) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Shift not found.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Cancellation Request
        |--------------------------------------------------------------------------
        */

        $cancelRequest->update([
            'status'           => $request->status,
            'recruiter_remark' => $request->recruiter_remark,
            'approved_by'      => $request->user()->id,
            'approved_at'      => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | APPROVED
        |--------------------------------------------------------------------------
        */

        if ($request->status == 'approved') {

            $shift->update([
                'status'              => 'pending',
                'booked_candidate_id' => null,
            ]);

            FillinShiftResponse::where('fillin_shift_id', $shift->id)
                ->where('candidate_id', $cancelRequest->candidate_id)
                ->update([
                    'response'     => 'cancelled',
                    'responded_at' => now(),
                ]);

            FillinShiftResponse::where('fillin_shift_id', $shift->id)
                ->where('candidate_id', '!=', $cancelRequest->candidate_id)
                ->where('response', 'expired')
                ->update([
                    'response'     => 'available',
                    'responded_at' => now(),
                ]);

        }

        /*
        |--------------------------------------------------------------------------
        | REJECTED
        |--------------------------------------------------------------------------
        */

        else {

            $shift->update([
                'status'              => 'confirmed',
                'booked_candidate_id' => $cancelRequest->candidate_id,
            ]);

            FillinShiftResponse::where('fillin_shift_id', $shift->id)
                ->where('candidate_id', $cancelRequest->candidate_id)
                ->update([
                    'response'     => 'confirmed',
                    'responded_at' => now(),
                ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Commit Transaction
        |--------------------------------------------------------------------------
        */

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Notify Candidate
        |--------------------------------------------------------------------------
        */

        try {

            $this->sendCancellationStatusNotification(
                $shift,
                $cancelRequest->candidate_id,
                $request->status
            );

        } catch (\Exception $e) {

            Log::error(
                'Cancellation notification failed: ' .
                $e->getMessage()
            );
        }

        return response()->json([
            'status'  => true,
            'message' => $request->status == 'approved'
                ? 'Cancellation request approved successfully.'
                : 'Cancellation request rejected successfully.',
            'data' => [
                'request_id'          => $cancelRequest->id,
                'request_status'      => $cancelRequest->status,
                'shift_id'            => $shift->id,
                'shift_status'        => $shift->status,
                'booked_candidate_id' => $shift->booked_candidate_id,
            ]
        ], 200);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Update Cancellation Status Error', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}
