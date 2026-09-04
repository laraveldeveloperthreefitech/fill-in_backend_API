<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Hash};
use App\Models\{Candidate,Education,WorkExperiance,Recruiter};
use App\Http\Traits\{RestResponse,HelperTrait};
use App\Http\Requests\Candidate\{RegisterRequest,OtpRequest,ChangePasswordRequest,ProfileRequest};
use App\Mail\SendOtpMail;
use App\Http\Resources\Candidate\CandidateResource;
use App\Services\FirebaseNotificationService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\CandidateFCMToken;
 use Illuminate\Support\Facades\DB;

class CandidateAuthController extends Controller
{
    use HelperTrait,RestResponse;
    
    public function __construct()
    {
        $this->fcm = new FirebaseNotificationService();
    }
    /**
     * login
     * Developer : Faizan khan
     * @param  mixed $request
     * @return void
     */
    // public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|exists:candidates,email',
    //         'password'  => 'required',
    //         // 'fcm_token' => 'required',
    //         'device_id' => 'required'
    //    ]);

    //     if ($validator->fails()) {
    //         return $this->customValidationres($validator->errors()->first());
    //     }
    //     try{
    //         $data = Candidate::where('email', $request->email)->first();
    //         $credentials = $request->only('email', 'password');
    //         if (!$token = auth()->guard('candidate')->attempt($credentials))
    //             return $this->customErrorRes('Invalid credentials');

    //         $expiresIn = auth()->guard('candidate')->factory()->getTTL() * 43200;

    //         if (!$data->verified) 
    //             return $this->customErrorRes('Account not verified. Please verify your email.');

    //         if (!$data->status) 
    //             return $this->customErrorRes('Your account has been blocked by Fill-in. Please contact us for further assistance.');

    //         if ($request->filled('fcm_token')) {
    //             CandidateFCMToken::updateOrCreate(
    //                 ['device_id' => $request->device_id],
    //                 [
    //                     'candidate_id'  =>  $data->id,
    //                     'fcm_token'     => $request->fcm_token,
    //                 ]
    //             );
    //         }
          
    //         $token = ['token' => $token];
    //         return $this->successResWithOtherData('Login successful',$data,$token);
    //     }catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }

    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'     => 'required|email',
        'password'  => 'required',
        'device_id' => 'required',
    ]);

    if ($validator->fails()) {
        return $this->customValidationres($validator->errors()->first());
    }

    try {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('candidate')->attempt($credentials)) {
            return $this->customErrorRes('Invalid credentials');
        }

        /** @var \App\Models\Candidate $candidate */
        $candidate = Auth::guard('candidate')->user();

        if (! $candidate->verified) {
            Auth::guard('candidate')->logout();
            return $this->customErrorRes('Account not verified. Please verify your email.');
        }

        if (! $candidate->status) {
            Auth::guard('candidate')->logout();
            return $this->customErrorRes('Your account has been blocked by Fill-in. Please contact us for further assistance.');
        }

        if ($request->filled('fcm_token')) {
            CandidateFCMToken::updateOrCreate(
                ['device_id' => $request->device_id],
                [
                    'candidate_id' => $candidate->id,
                    'fcm_token'    => $request->fcm_token,
                ]
            );
        }

        return response()->json([
            'statusCode' => 200,
            'status'     => 'success',
            'message'    => 'Login successful',
            'token'      => $token,
        ]);
    } catch (\Exception $e) {
        return $this->getExceptionResponse($e);
    }
}

    
    /**
     * register recruiter
     *
     * @param  mixed $request
     * @return void
     */
    // public function register(RegisterRequest $request)
    // {
    //     try{
    //         $data = Candidate::create($request->requestData());
    //         if($data){
                
    //             return $this->customSuccessRes('Registration successful');
    //         }
    //         else
    //             return $this->customErrorRes('Somthing went wrong.Please try again!');
    //     }catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }

    public function register(RegisterRequest $request)
{
    try {
        Candidate::create($request->requestData());

        return $this->customSuccessRes('Registration successful.');

    } catch (\Exception $e) {
        return $this->getExceptionResponse($e);
    }
}
    
    /**
     * sendOtp for recruiter
     *
     * @param  mixed $request
     * @return void
     */
    public function sendOtp(OtpRequest $request)
    {
        try {
            // dd(auth()->guard('candidate')->check());
            // Generate a 6-digit OTP
            $otp    = random_int(1000, 9999);
            if(auth()->guard('candidate')->check()){
                auth()->guard('candidate')->user()->update($request->requestData($otp));
            }else{
                $data   = Candidate::where('email', $request->email)->update($request->requestData($otp));
            }
            if($request->type == 'phone'){
                $message    = "Your Verification Code is ".$otp;
                $send_otp   = $this->send_otp_to_phone($request->phone,$message);
            }else{
                $mail   = \Mail::to($request->email)->send(new SendOtpMail($otp));
            }
            

            return $this->customSuccessRes('OTP sent successfully');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * verifyOtp for recruiter
     *
     * @param  mixed $request
     * @return void
     */
    public function verifyOtp(OtpRequest $request)
    {
        try {
            // Retrieve the recruiter by email
            $data       = Candidate::where('email', $request->email)->first();
            $expiryDate = date('Y-m-d H:i:s',strtotime($data->expire_otp));
            if (now()->greaterThan($expiryDate) || $data->otp != $request->otp) {
                return $this->customErrorRes('Invalid OTP.');
            }

            $data->verified     = 1;
            $data->otp          = null;
            $data->expire_otp   = null;
            $data->status       = 1;
            $data->save();
           
            // Return the verified data data using the resource
            return $this->customSuccessRes('OTP verified successfully');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * changePassword
     *
     * @param  mixed $request
     * @return void
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $data       = Candidate::where('email', $request->email)->update(['password'  => Hash::make($request->password)]);
            if($data)
                return $this->customSuccessRes('Password Change Successfully');
            else
                return $this->customErrorRes('Password Change Successfully');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * viewProfile
     *
     * @param  mixed $request
     * @return void
     */
    public function viewProfile(Request $request)
    {
        try {
            $data       = Candidate::with('specialization','languages')->find(auth()->user()->id);
            if($data)
                return $this->recordFoundWithResponse(new CandidateResource($data));
            else
                return $this->recordNotFoundResponse();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
    
    /**
     * updateProfile
     *
     * @param  mixed $request
     * @return void
     */
   

public function updateProfile(ProfileRequest $request)
{
    DB::beginTransaction();

    try {

        $candidate = auth()->guard('candidate')->user();

        if (!$candidate) {
            return response()->json([
                'status'  => false,
                'message' => 'Candidate not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE CANDIDATE
        |--------------------------------------------------------------------------
        */

        $candidate->update(
            $request->requestData()
        );

        /*
        |--------------------------------------------------------------------------
        | EXTRACT IDS
        |--------------------------------------------------------------------------
        */

        $softwareIds      = $this->extractIds($request->software_experiance);
        $qualificationIds = $this->extractIds($request->qualification);
        $languageIds      = $this->extractIds($request->language);
        $vaccinationIds   = $this->extractIds($request->vaccination);

        /*
        |--------------------------------------------------------------------------
        | SYNC RELATIONS
        |--------------------------------------------------------------------------
        */

        $candidate->software_experiance()->sync($softwareIds);
        $candidate->qualification()->sync($qualificationIds);
        $candidate->languages()->sync($languageIds);
        $candidate->Vaccination()->sync($vaccinationIds);

        /*
        |--------------------------------------------------------------------------
        | SEND NOTIFICATION
        |--------------------------------------------------------------------------
        */

        $this->sendRecNotification($candidate);

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | LOAD RELATIONS
        |--------------------------------------------------------------------------
        */

        $candidate->load([
            'software_experiance:id,name',
            'qualification:id,name',
            'languages:id,name',
            'Vaccination:id,name',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Profile Updated Successfully',
            'data'    => new CandidateResource($candidate)
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile()
        ], 500);
    }
}

/*
|--------------------------------------------------------------------------
| HELPER METHOD
|--------------------------------------------------------------------------
*/

private function extractIds($items): array
{
    return collect($items ?? [])
        ->map(function ($item) {

            if (is_numeric($item)) {
                return (int) $item;
            }

            if (is_array($item)) {
                return $item['id'] ?? null;
            }

            if (is_object($item)) {
                return $item->id ?? null;
            }

            return null;

        })
        ->filter()
        ->unique()
        ->values()
        ->toArray();
}
    
//     public function updateProfile(ProfileRequest $request)
// {
//     try {

//         $candidate = Candidate::find(auth()->guard('candidate')->id());

//         if (!$candidate) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Candidate not found'
//             ], 404);
//         }

//         /*
//         |--------------------------------------------------------------------------
//         | UPDATE PROFILE
//         |--------------------------------------------------------------------------
//         */

//         $candidate->update($request->requestData());

//         /*
//         |--------------------------------------------------------------------------
//         | EXTRACT IDS
//         |--------------------------------------------------------------------------
//         */

//         $softwareIds = collect($request->software_experiance ?? [])
//             ->map(function ($item) {
//                 if (is_numeric($item)) {
//                     return (int) $item;
//                 }

//                 if (is_array($item)) {
//                     return $item['id'] ?? null;
//                 }

//                 if (is_object($item)) {
//                     return $item->id ?? null;
//                 }

//                 return null;
//             })
//             ->filter()
//             ->values()
//             ->toArray();

//         $qualificationIds = collect($request->qualification ?? [])
//             ->map(function ($item) {
//                 if (is_numeric($item)) {
//                     return (int) $item;
//                 }

//                 if (is_array($item)) {
//                     return $item['id'] ?? null;
//                 }

//                 if (is_object($item)) {
//                     return $item->id ?? null;
//                 }

//                 return null;
//             })
//             ->filter()
//             ->values()
//             ->toArray();

//         $languageIds = collect($request->language ?? [])
//             ->map(function ($item) {
//                 if (is_numeric($item)) {
//                     return (int) $item;
//                 }

//                 if (is_array($item)) {
//                     return $item['id'] ?? null;
//                 }

//                 if (is_object($item)) {
//                     return $item->id ?? null;
//                 }

//                 return null;
//             })
//             ->filter()
//             ->values()
//             ->toArray();

//         $vaccinationIds = collect($request->vaccination ?? [])
//             ->map(function ($item) {
//                 if (is_numeric($item)) {
//                     return (int) $item;
//                 }

//                 if (is_array($item)) {
//                     return $item['id'] ?? null;
//                 }

//                 if (is_object($item)) {
//                     return $item->id ?? null;
//                 }

//                 return null;
//             })
//             ->filter()
//             ->values()
//             ->toArray();

//         /*
//         |--------------------------------------------------------------------------
//         | SYNC RELATIONS
//         |--------------------------------------------------------------------------
//         */

//         $candidate->software_experiance()->sync($softwareIds);

//         $candidate->qualification()->sync($qualificationIds);

//         $candidate->languages()->sync($languageIds);

//         $candidate->Vaccination()->sync($vaccinationIds);

//         /*
//         |--------------------------------------------------------------------------
//         | SEND NOTIFICATION
//         |--------------------------------------------------------------------------
//         */

//         $this->sendRecNotification($candidate);

//         /*
//         |--------------------------------------------------------------------------
//         | LOAD RELATIONS
//         |--------------------------------------------------------------------------
//         */

//         $candidate->load([
//             'software_experiance',
//             'qualification',
//             'languages',
//             'Vaccination'
//         ]);

//         return response()->json([
//             'status' => true,
//             'message' => 'Profile Updated Successfully',
//             'data' => new CandidateResource($candidate)
//         ], 200);

//     } catch (\Exception $e) {

//         return response()->json([
//             'status' => false,
//             'message' => $e->getMessage(),
//             'line' => $e->getLine(),
//             'file' => $e->getFile()
//         ], 500);
//     }
// }



     private function sendRecNotification($data)
        {
     		 
            $profession = $data->specialization_name ? $data->specialization_name : null;
            if($profession){
           
                    $ids = Recruiter::where(function ($query) use ($profession) {
                    $query->WhereHas('lookingFor', function ($q) use ($profession) {
                            $q->where('name', $profession);
                    });
                })->pluck('id')->toArray();
             $icon = $data->profile ? 
         $data->profile : ''; 
            $this->fcm->notifyRecruiters(
                    $ids,
                    $data->name . ' is a new candidate that matches your requirements.',
                    'New Candidate Match',
                    '',$icon,'New Profile Match',$data->id,'candidate'
                );
            } 
        }
}
