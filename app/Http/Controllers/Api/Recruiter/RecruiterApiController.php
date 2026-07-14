<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\Recruiter\{
    RatingRequest,
    CandidateReportRequest
};

use App\Http\Requests\SupportRequest;
use App\Http\Traits\RestResponse;

use App\Models\{
    CandidateReview,
    CandidateReviewAnswer,
    CandidateFeedbackQuestion,
    ClinicReview,
    ReportOnCandidate,
    ClinicSupport,
    Faq,
    Candidate,
    Recruiter,
    RecruiterSearch,
    RecruiterRecentSearch,
    Chat
};

use App\Http\Resources\{
    CandidateListResource,
    NotificationResource
};

use App\Http\Resources\Recruiter\allCandidateChatResource;

use App\Services\FirebaseNotificationService;


class RecruiterApiController extends Controller
{
    use RestResponse;
    
    public function __construct()
    {
        $this->fcm = new FirebaseNotificationService();
    }
    /**
     * addCandidateRating
     * Developer : Faizan khan
     * @param  mixed $request
     * @return void
     */
    // public function addCandidateRating(RatingRequest $request){
    //     try{
    //         $data = CandidateReview::create($request->requestData());
    //         if($data){
    //             $this->sendNotification($request->candidate_id);
               
    //              return $this->newRecordSaveResponse($data);
    //         }  
    //         else
    //             return $this->recordNotFoundResponse();
    //     }catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }
    
      public function addCandidateRating(RatingRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = CandidateReview::create($request->requestData());

            if (!$data) {
                DB::rollBack();
                return $this->recordNotFoundResponse();
            }

            if ($request->has('answers') && is_array($request->answers)) {
                foreach ($request->answers as $item) {
                    CandidateReviewAnswer::create([
                        'candidate_review_id' => $data->id,
                        'candidate_feedback_question_id' => $item['question_id'],
                        'answer' => $item['answer'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $this->sendNotification($request->candidate_id);

            $data->load('answers.question');

            return $this->newRecordSaveResponse($data);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->getExceptionResponse($e);
        }
    }

    public function candidateFeedbackQuestions()
{
    try {
        $questions = CandidateFeedbackQuestion::where('status', 1)
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


public function candidateRanking()
{
    try {
        $rankings = DB::table('candidates as c')
            ->leftJoin('candidate_reviews as cr', 'cr.candidate_id', '=', 'c.id')
            ->leftJoin('candidate_review_answers as cra', 'cra.candidate_review_id', '=', 'cr.id')
            ->select(
                'c.id as candidate_id',
                'c.name as candidate_name',
                DB::raw('COALESCE(AVG(cr.rate), 0) as average_rating'),
                DB::raw("SUM(CASE WHEN LOWER(cra.answer) = 'yes' THEN 1 ELSE 0 END) as yes_points"),
                DB::raw('COUNT(cra.id) as total_answers'),
                DB::raw("
                    (
                        COALESCE(AVG(cr.rate), 0) +
                        SUM(CASE WHEN LOWER(cra.answer) = 'yes' THEN 1 ELSE 0 END)
                    ) as final_rank
                ")
            )
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('final_rank')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Candidate ranking fetched successfully.',
            'data' => $rankings,
        ], 200);

    } catch (\Exception $e) {
        return $this->getExceptionResponse($e);
    }
}


public function candidateRankingDetail($candidateId)
{
    try {
        $reviews = CandidateReview::with('answers.question')
            ->where('candidate_id', $candidateId)
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
            'message' => 'Candidate ranking detail fetched successfully.',
            'data' => [
                'candidate_id' => $candidateId,
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
        $data = Candidate::find($id);
        $icon = $data->profile ? 
         $data->profile : ''; 

        $this->fcm->notifyCandidates(
                $id,
                auth()->user()->name . ' has rate your porfile ',
                'Rating Notification',
                '',$icon,'Rating',$id,'candidate'
            );
    } 
    
    /**
     * reportToCandidate
     *
     * @param  mixed $request
     * @return void
     */
    public function reportToCandidate(CandidateReportRequest $request){
        try{
            $data = ReportOnCandidate::create($request->requestData());
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
            if(!auth()->user()->clinic){
                return $this->customErrorRes('First, you must be complete your profile.');
            }
            $data = ClinicSupport::create($request->requestClinic());
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
            $data = Faq::whereStatus(1)->whereRole(0)->get();
            if($data)
                return $this->recordFoundWithResponse($data);
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
            $data       = auth()->guard('recruiter')->user();
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

    public function candidateByProfession(Request $request){
        try{
            $data = Candidate::where('specialization_name',$request->name)->get();
            if($data)
                return $this->newRecordSaveResponse(CandidateListResource::collection($data));
            else
                return $this->recordNotFoundResponse();
        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    public function NotificationList(Request $request){
        try{
            auth()->guard('recruiter')->user()->notifications()->update(['is_read' => 1]);
            $data = auth()->guard('recruiter')->user()->notifications;
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
       public function searchTerms(){
        try{ 
            $popular = RecruiterSearch::select('term')->orderByDesc('count')->take(6)->get();
            $recent  = RecruiterRecentSearch::select('term')->where('recruiter_id',auth()->guard('recruiter')->user()->id)->get();
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

   public function recruiterChatUser(Request $request)
    {
        try {
           
            $recruiterId = auth()->guard('recruiter')->user()->id;

           $chats = Chat::with('candidates')->where('recruiter_id', $recruiterId)
            ->latest()->get()
            ->groupBy('canditidate_id')
                ->map(function ($messages) {
                    $latest = $messages->first(); // latest message due to latest()
                   $unseenCount = $messages->where('sendBy','candidate')->where('status', 0)->count();
                    $latest->unseen_count = $unseenCount;
                    return $latest;
                })
                ->values(); // reset collection keys
            if($chats)
                return $this->recordFoundWithResponse(allCandidateChatResource::collection($chats));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

}
