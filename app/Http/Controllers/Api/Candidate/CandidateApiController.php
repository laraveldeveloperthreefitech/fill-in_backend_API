<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\Candidate\{
    RatingRequest,
    JobReportRequest,
    RecruiterReportRequest
};

use App\Http\Requests\SupportRequest;

use App\Http\Traits\{
    HelperTrait,
    RestResponse
};

use App\Models\{
    ClinicReview,
    ClinicReviewAnswer,
    ClinicFeedbackQuestion,
    Clinic,
    ReportOnJob,
    CandidateSupport,
    Faq,
    CandidateSearch,
    CandidateRecentSearch,
    JobListing,
    Specialization,
    Department,
    Recruiter,
    Chat,
    ReportOnRecruiter
};

use App\Http\Resources\Recruiter\{
    RecruiterResource
};

use App\Http\Resources\Candidate\{
    JobListResource,
    SpecializationResource
};

use App\Http\Resources\{
    NotificationResource,
    ModifyChatResource
};

use App\Mail\SendOtpMail;

use App\Services\FirebaseNotificationService;


class CandidateApiController extends Controller
{
    use HelperTrait,RestResponse;
    
     public function __construct()
    {
        $this->fcm = new FirebaseNotificationService();
    }
    /**
     * viewClinic 
     * Devloper : Faizan khan
     * @param  mixed $id
     * @return void
     */
    public function viewClinic($id){
        try{ 
            $data = Recruiter::find($id);
            if($data)
                return $this->recordFoundWithResponse(new RecruiterResource($data));
            else
                return $this->recordNotFoundResponse();

        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * addClinicRating
     * Faizan khan
     * @param  mixed $request
     * @return void
     */
    // public function addClinicRating(RatingRequest $request){         
    //     try{     
    //         $data = ClinicReview::create($request->requestData());
    //         if($data){
    //             $this->sendNotification($request->recruiter_id);
    //             return $this->newRecordSaveResponse($data);
    //         }
                
    //         else
    //             return $this->recordNotFoundResponse();
    //     }catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }
    
      public function addClinicRating(RatingRequest $request)
{
    DB::beginTransaction();

    try {
        $data = ClinicReview::create($request->requestData());

        if (!$data) {
            DB::rollBack();
            return $this->recordNotFoundResponse();
        }

        if ($request->has('answers') && is_array($request->answers)) {
            foreach ($request->answers as $item) {
                ClinicReviewAnswer::create([
                    'clinic_review_id' => $data->id,
                    'clinic_feedback_question_id' => $item['question_id'],
                    'answer' => $item['answer'] ?? null,
                ]);
            }
        }

        DB::commit();

        $this->sendNotification($request->recruiter_id);

        $data->load('answers.question');

        return $this->newRecordSaveResponse($data);

    } catch (\Exception $e) {
        DB::rollBack();
        return $this->getExceptionResponse($e);
    }
}

public function clinicFeedbackQuestions()
{
    try {
        $questions = ClinicFeedbackQuestion::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Feedback questions fetched successfully.',
            'data' => $questions,
        ], 200);

    } catch (\Exception $e) {
        return $this->getExceptionResponse($e);
    }
}

public function recruiterRanking()
{
    try {
        $rankings = DB::table('recruiters as r')
            ->leftJoin('clinic_reviews as clr', 'clr.recruiter_id', '=', 'r.id')
            ->leftJoin('clinic_review_answers as clra', 'clra.clinic_review_id', '=', 'clr.id')
            ->select(
                'r.id as recruiter_id',
                'r.name as recruiter_name',
                DB::raw('COALESCE(AVG(clr.rate), 0) as average_rating'),
                DB::raw("SUM(CASE WHEN LOWER(clra.answer) = 'yes' THEN 1 ELSE 0 END) as yes_points"),
                DB::raw('COUNT(clra.id) as total_answers'),
                DB::raw("
                    (
                        COALESCE(AVG(clr.rate), 0) +
                        SUM(CASE WHEN LOWER(clra.answer) = 'yes' THEN 1 ELSE 0 END)
                    ) as final_rank
                ")
            )
            ->groupBy('r.id', 'r.name')
            ->orderByDesc('final_rank')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Recruiter ranking fetched successfully.',
            'data' => $rankings,
        ], 200);

    } catch (\Exception $e) {
        return $this->getExceptionResponse($e);
    }
}


public function recruiterRankingDetail($recruiterId)
{
    try {
        $reviews = ClinicReview::with('answers.question')
            ->where('recruiter_id', $recruiterId)
            ->get();

        $averageRating = $reviews->avg('rate') ?? 0;

        $yesPoints = 0;

        foreach ($reviews as $review) {
            foreach ($review->answers as $answer) {
                if (strtolower($answer->answer) === 'yes') {
                    $yesPoints++;
                }
            }
        }

        $finalRank = $averageRating + $yesPoints;

        return response()->json([
            'status' => true,
            'message' => 'Recruiter ranking detail fetched successfully.',
            'data' => [
                'recruiter_id' => $recruiterId,
                'average_rating' => round($averageRating, 2),
                'yes_points' => $yesPoints,
                'final_rank' => round($finalRank, 2),
                'reviews' => $reviews,
            ],
        ], 200);

    } catch (\Exception $e) {
        return $this->getExceptionResponse($e);
    }
}


    private function sendNotification($id){
        $data = Recruiter::with('clinic')->find($id);
        $icon = $data->clinic && $data->clinic->profile ? 
        $data->clinic->profile : ''; 
         $this->fcm->notifyRecruiters(
                $id,
                auth()->user()->name . ' has rate your porfile ',
                'Rating Notification',
                '',$icon,'Rating',$id,'recruiter'
            );
    } 
    
    /**
     * reportToJob
     *
     * @param  mixed $request
     * @return void
     */
    public function reportToJob(JobReportRequest $request){
        try{
            $data = ReportOnJob::create($request->requestData());
            if($data)
                return $this->newRecordSaveResponse($data);
            else
                return $this->recordNotFoundResponse();
        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    public function reportToRecruiter(RecruiterReportRequest $request){
        try{
            $data = ReportOnRecruiter::create($request->requestData());
            if($data)
                return $this->newRecordSaveResponse($data);
            else
                return $this->recordNotFoundResponse();
        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * SupportInfo
     *
     * @param  mixed $request
     * @return void
     */
    public function SupportInfo(SupportRequest $request){
        try{
            $data = CandidateSupport::create($request->requestCandidate());
            if($data)
                return $this->newRecordSaveResponse($data);
            else
                return $this->recordNotFoundResponse();
        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * Faq
     *
     * @return void
     */
    public function Faq(){
        try{ 
            $data = Faq::whereStatus(1)->orderByDesc('id')->whereRole(1)->get();
            if($data)
                return $this->recordFoundWithResponse($data);
            else
                return $this->recordNotFoundResponse();

        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * searchTerms
     *
     * @return void
     */
    public function searchTerms(){
        try{ 
            $popular = CandidateSearch::select('term')->orderByDesc('count')->take(6)->get();
            $recent  = CandidateRecentSearch::select('term')->where('candidate_id',auth()->guard('candidate')->user()->id)->get();
            if($popular || $recent){
                $data['popular'] = $popular;
                $data['recent']  = $recent;
                return $this->recordFoundWithResponse($data);
            }
            else
                return $this->recordNotFoundResponse();
        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    
    
    /**
     * changeEmail
     *
     * @param  mixed $request
     * @return void
     */
    public function changeEmail(Request $request)
    {
        try {
            $data       = auth()->guard('candidate')->user();
            $expiryDate = date('Y-m-d H:i:s',strtotime($data->expire_otp));
            if (now()->greaterThan($expiryDate) || $data->otp != $request->otp) {
                return $this->customErrorRes('Invalid OTP.');
            }
            $update = $data->update(['email' => $request->email,'otp' => null,'expire_otp' => null]);
            if($update)
                return $this->customSuccessRes('Email Change Successfully');
            else
                return $this->customErrorRes('Email Change Successfully');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * jobByProfession
     *
     * @param  mixed $request
     * @return void
     */
    // public function jobByProfession(Request $request){
    //     try{
    //         $data = JobListing::whereHas('specialization',function($query){
    //             $query->where('name',$request->name);
    //         })->get();
    //         if($data)
    //             return $this->newRecordSaveResponse(JobListResource::collection($data));
    //         else
    //             return $this->recordNotFoundResponse();
    //     }catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }
    
    public function jobByProfession(Request $request)
{
    try {

        $name = $request->query('name');

        $data = JobListing::whereHas('specialization', function ($query) use ($name) {
            $query->where('name', $name);
        })->get();

        return $this->recordFoundWithResponse(JobListResource::collection($data));

    } catch (\Exception $e) {
        return $this->getExceptionResponse($e);
    }
}

    public function NotificationList(Request $request){
        try{
        	auth()->guard('candidate')->user()->notifications()->update(['is_read' => 1]);
        	auth()->guard('candidate')->user()->notifications()->update(['is_read' => 1]);
            $data = auth()->guard('candidate')->user()->notifications;
            $grouped = $data->groupBy(fn($item) => $item->created_at->isToday() ? 'Today' 
                                : ($item->created_at->isYesterday() ? 'Yesterday' 
                                : $item->created_at->format('d M Y')));

             // Transform grouped data into desired structure
            $final = $grouped->map(function ($items, $date) {
                return [
                    'datelabel' => $date,
                    'data' => NotificationResource::collection($items),
                ];
            })->values(); // Reset array keys

            return $final->isNotEmpty()
                ? $this->recordFoundWithResponse($final)
                : $this->recordNotFoundResponse();   
        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    //   public function candidateChatUser(Request $request)
    // {
    //     try {
    //         $candidateId = auth()->guard('candidate')->user()->id;

    //         $chats = Chat::with('recruiters')
    //             ->where('canditidate_id', $candidateId)
    //             ->latest()
    //             ->get()
    //             ->groupBy('recruiter_id')
    //             ->map(function ($messages) {
    //                 $latest = $messages->first(); // latest message due to latest()
    //                 $unseenCount = $messages->where('sendBy','recruiter')->where('status', 0)->count();
    //                 $latest->unseen_count = $unseenCount;
    //                 return $latest;
    //             })
    //             ->values(); // reset collection keys

    //         if ($chats->isNotEmpty()) {
    //             return $this->recordFoundWithResponse(ModifyChatResource::collection($chats));
    //         } else {
    //             return $this->recordNotFoundResponse();
    //         }
    //     } catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }
    
    public function candidateChatUser(Request $request)
{
    try {
        $candidate = auth()->guard('candidate')->user();

        if (!$candidate) {
            return response()->json([
                'statusCode' => 401,
                'status' => 'failed',
                'message' => 'Unauthorized'
            ]);
        }

        $candidateId = $candidate->id;

        $chats = Chat::with('recruiters')
            ->where('canditidate_id', $candidateId) // ✅ fixed
            ->latest()
            ->get()
            ->groupBy('recruiter_id')
            ->map(function ($messages) {
                $latest = $messages->first();

                $unseenCount = $messages
                    ->where('sendBy', 'recruiter')
                    ->where('status', 0)
                    ->count();

                $latest->unseen_count = $unseenCount;

                return $latest;
            })
            ->values();

        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'message' => 'Chat users fetched successfully',
            'data' => ModifyChatResource::collection($chats)
        ]);

    } catch (\Exception $e) {
        return $this->getExceptionResponse($e);
    }
}

}
