<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Resources\{DropdownResource,SpecializationResource,RequireDocumentResource};
use App\Models\{Specialization,EmploymentType,RequireDocument,Setting,Qualification,Language,Software,Vaccination,WorkLocationRange,PracticeRole,JobListing,Candidate,Chat};
use App\Http\Traits\{RestResponse,HelperTrait};
use App\Http\Resources\CandidateListResource;
use App\Http\Resources\Candidate\{JobListResource,SpecializationResource as ProfessionResource};
use App\Http\Requests\{chatRequest};
use App\Events\ChatMessageSent;
use App\Http\Resources\{ChatResource,ModifyChatResource};
use App\Services\FirebaseNotificationService;

class CommonController extends Controller
{
    use RestResponse,HelperTrait;
    
     public function __construct()
    {
        $this->fcm = new FirebaseNotificationService();
    }
    /**
     * getDropdownData
     * Developer : Faizan khan
     * @param  mixed $request
     * @return void
     */
    public function getDropdownData(Request $request){
        try { 
            $profession      = Specialization::whereStatus(1)->get();
            $employmentTypes = EmploymentType::whereStatus(1)->get();
        	$document		 = RequireDocument::whereStatus(1)->get();
            $qualification   = Qualification::whereStatus(1)->get();
            $language        = Language::whereStatus(1)->get();
            $software        = Software::whereStatus(1)->get();
            $vaccination     = Vaccination::whereStatus(1)->get();
            $locationRange   = WorkLocationRange::whereStatus(1)->get();
            $practiceRole    = PracticeRole::whereStatus(1)->get();
            return $this->recordFoundWithResponse([
                'profession'       => DropdownResource::collection($profession),
                'employment_types' => DropdownResource::collection($employmentTypes),
                'require_document' => DropdownResource::collection($document),
                'qualification'    => DropdownResource::collection($qualification),
                'language'         => DropdownResource::collection($language),
                'software'         => DropdownResource::collection($software),
                'vaccination'      => DropdownResource::collection($vaccination),
                'locationRange'    => DropdownResource::collection($locationRange),
                'practiceRole'     => DropdownResource::collection($practiceRole),

            ]);
            
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    public function getSpecialization($id){
        try { 
            // $specializations = Specialization::where('department_id',$id)->whereStatus(1)->get();
            $specializations = Specialization::whereStatus(1)->get();
    
            if ($specializations->isNotEmpty()) {
                return $this->recordFoundWithResponse([
                    'specializations'       => DropdownResource::collection($specializations),
                ]);
            } else {
                return $this->recordNotFoundResponse();
            }
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    public function setting()
    {
        try {
            $data = Setting::first();
            if($data)
                return $this->recordFoundWithResponse($data);
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

     public function changePhone(Request $request)
        {
            try {
                $data       = auth()->guard('candidate')->user() ??  auth()->guard('recruiter')->user();
                $expiryDate = date('Y-m-d H:i:s',strtotime($data->expire_otp));
                if (now()->greaterThan($expiryDate) || $data->otp != $request->otp) {
                    return $this->customErrorRes('Invalid OTP.');
                }
                if($request->phone){
                    $update = $data->update(['phone_verified' => 1,'phone' => $request->phone,'otp' => null,'expire_otp' => null]);
                }else{
                    $update = $data->update(['phone_verified' => 1,'otp' => null,'expire_otp' => null]);
                }
            
                if($update)
                    return $this->customSuccessRes('Phone Change Successfully');
                else
                    return $this->customErrorRes('Phone Change Successfully');
            } catch (\Exception $e) {
                return $this->getExceptionResponse($e);
            }
        }

    public function dashboard(Request $request){
        try{
            $data           = JobListing::ApiSearch($request)->with('clinic','specialization','employmentTypes')
                                ->withCount('candidates')->whereStatus(1)->get();
            $specialization = Specialization::withCount('job')->withCount('candidate')->whereStatus(1)->get();
            $candidate      = Candidate::ApiSearch($request)->get();
            if(count($data) > 0 || count($specialization) > 0 || count($candidate) > 0)
                return $this->recordFoundWithResponse([
                    'specialization'    => ProfessionResource::collection($specialization),
                    'jobs'              => JobListResource::collection($data),
                    'candidate'         => CandidateListResource::collection($candidate),
                ]);
            else
                return $this->recordNotFoundResponse();
        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    public function candidateProfile()
    {
        try {
            $profile = auth()->guard('candidate')->user()->profile ? 
             config('filepaths.candidate.public_url') . auth()->guard('candidate')->user()->profile : '';
               $unreadCount = auth()->guard('candidate')->user()->notifications()->where('is_read',0)->count();
             $data = [
                'id' => auth()->guard('candidate')->user()->id,
                'profile' => $profile,
                'unreadNotification' => $unreadCount,
                'is_completed' => auth()->guard('candidate')->user()->specialization_name ? 1 : 0
             ];
            if($data)
                return $this->recordFoundWithResponse($data);
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

     public function recruiterProfile()
    {
        try {
            $profile = auth()->guard('recruiter')->user()->clinic &&  auth()->guard('recruiter')->user()->clinic->profile ? 
             config('filepaths.recruiter.public_url') . auth()->guard('recruiter')->user()->clinic->profile : '';
             $unreadCount = auth()->guard('recruiter')->user()->notifications()->where('is_read',0)->count();
             $data = [
                'id' => auth()->guard('recruiter')->user()->id,
                'profile' => $profile,
                'unreadNotification' => $unreadCount,
                'is_completed' => auth()->guard('recruiter')->user()->clinic ? 1 : 0
             ];
            if($data)
                return $this->recordFoundWithResponse($data);
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

     public function profession()
    {
       try {
             $specialization = Specialization::withCount('job')->withCount('candidate')->whereStatus(1)->get();
            if($specialization)
                return $this->recordFoundWithResponse(ProfessionResource::collection($specialization));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    public function sendMessage(chatRequest $request)
    {
       try {
            $chat = Chat::create($request->requestData());
            event(new ChatMessageSent($chat));
            if($chat->sendBy === 'candidate'){
                $this->sendChatRecNotification($chat);
            }else{
                $this->sendChatCandNotification($chat);
            }
            return $this->customSuccessRes('message sent Successfully');
         } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    private function sendChatRecNotification($chat){
        $candidate = auth()->guard('candidate')->user();
        $icon =  $candidate->profile ? config('filepaths.candidate.public_url') . $candidate->profile : '';
        $this->fcm->notifyRecruiters(
            $chat->recruiter_id,
        	'Recieved message from' . ' ' . $candidate->name ,
            $chat->message,
            '',$icon,
           'chat',
            $candidate->id,$candidate->name
        );
    } 

    private function sendChatCandNotification($chat){
        $recruiter = auth()->guard('recruiter')->user();
        $icon =  $recruiter->clinic ? ($recruiter->clinic->profile ?  config('filepaths.recruiter.public_url') . $recruiter->clinic->profile : '') : '';
        $this->fcm->notifyCandidates(
            $chat->canditidate_id,
            'Recieved message from' . ' ' . $recruiter->name ,
            $chat->message,
            '',$icon,
           'chat',
            auth()->guard('recruiter')->user()->id,$recruiter->name
        );
    } 

    public function allChat(Request $request, $id)
    {
        try {
            $candidateId = auth()->guard('candidate')->id() ?? (int) $id;
            $recruiterId = auth()->guard('recruiter')->id() ?? (int) $id;

            $chats = Chat::where('canditidate_id', $candidateId)
                ->where('recruiter_id', $recruiterId)
                ->latest()
                ->get();

            $grouped = $chats->groupBy(fn($chat) => $chat->created_at->isToday() ? 'Today' 
                                : ($chat->created_at->isYesterday() ? 'Yesterday' 
                                : $chat->created_at->format('d M Y')));

           $final = collect($grouped)->flatMap(function ($messages, $date) {
    			return $messages->map(fn($msg) => new ChatResource($msg))
                    ->concat([['datelabel' => $date]]);
			})->values();
            return $final->isNotEmpty()
                ? $this->recordFoundWithResponse($final)
                : $this->recordNotFoundResponse();
                
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

      public function chatMarkAsRead(Request $request, $id)
    {
        try {
            $candidateId = auth()->guard('candidate')->id() ?? (int) $id;
            $recruiterId = auth()->guard('recruiter')->id() ?? (int) $id;
            $sendBy      = auth()->guard('candidate')->check() ? 'recruiter' : 'candidate';

            $chats = Chat::where('canditidate_id', $candidateId)
                ->where('recruiter_id', $recruiterId)->where('sendBy',$sendBy)
                ->update(['status' => 1]);
                
            return $chats
                ? $this->recordUpdate()
                : $this->recordNotFoundResponse();
                
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    

    public function vaccinations()
    {
        try {
            $vaccinations = Vaccination::whereStatus(1)->get();
            if ($vaccinations)
                return $this->recordFoundWithResponse(DropdownResource::collection($vaccinations));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
}



public function language()
    {
        try {
            $language = Language::whereStatus(1)->get();
            if ($language)
                return $this->recordFoundWithResponse(DropdownResource::collection($language));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }

}

public function branch()
    {
        try {
            $branch = Branch::whereStatus(1)->get();
            if ($branch)
                return $this->recordFoundWithResponse(DropdownResource::collection($branch));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }

}
    
}