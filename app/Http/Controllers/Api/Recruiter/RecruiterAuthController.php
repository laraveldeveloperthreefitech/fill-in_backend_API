<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};
use App\Models\{Recruiter, Clinic, RecruiterFCMToken};
use App\Http\Traits\{RestResponse, HelperTrait};
use App\Http\Requests\Recruiter\{RegisterRequest, OtpRequest, ChangePasswordRequest, ProfileRequest};
use App\Http\Resources\Recruiter\{RecruiterResource};
use App\Mail\SendOtpMail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class RecruiterAuthController extends Controller
{
    use RestResponse, HelperTrait;
    /**
     * login recruiter
     * Developer : Faizan khan
     * @param  mixed $request
     * @return void
     */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:recruiters,email',
            'password'  => 'required',
            // 'fcm_token' => 'required',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->customValidationres($validator->errors()->first());
        }
        try {
            $data = Recruiter::where('email', $request->email)->first();
            $credentials = $request->only('email', 'password');
            if (!$token = auth()->guard('recruiter')->attempt($credentials))
                return $this->customErrorRes('Invalid credentials');

            $expiresIn = auth()->guard('recruiter')->factory()->getTTL() * 43200;

            if (!$data->verified)
                return $this->customErrorRes('Account not verified. Please verify your email.');

            if (!$data->status)
                return $this->customErrorRes('Your account has been blocked by Fill-in. Please contact us for further assistance.');
            if ($request->filled('fcm_token')) {
                RecruiterFCMToken::updateOrCreate(
                    ['device_id' => $request->device_id],
                    [
                        'recruiter_id'  =>  $data->id,
                        'fcm_token'     => $request->fcm_token,
                    ]
                );
            }
            $token = ['token' => $token];
            return $this->successResWithOtherData('Login successful', $data, $token);
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
    public function register(RegisterRequest $request)
    {
        try {
            $data = Recruiter::create($request->requestData());
            if ($data)
                return $this->recordFoundWithResponse($data);
            // return $this->customSuccessRes('Registration successful');
            else
                return $this->customErrorRes('Somthing went wrong.Please try again!');
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
    // public function sendOtp(OtpRequest $request)
    // {
    //     try {
    //         // Generate a 6-digit OTP
    //         $otp    = random_int(1000, 9999);
    //         if (auth()->guard('recruiter')->check()) {
    //             auth()->guard('recruiter')->user()->update($request->requestData($otp));
    //         } else {
    //             $data   = Recruiter::where('email', $request->email)->update($request->requestData($otp));
    //         }
    //         if ($request->type == 'phone') {
    //             $message    = "Your Verification Code is " . $otp;
    //             $send_otp   = $this->send_otp_to_phone($request->phone, $message);
    //         } else {
    //             $mail   = \Mail::to($request->email)->send(new SendOtpMail($otp));
    //         }


    //         return $this->customSuccessRes('OTP sent successfully');
    //     } catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }
    
      public function sendOtp(OtpRequest $request)
{
    try {

        // Generate OTP
        $otp = rand(1000, 9999);

        // OTP expiry (10 minutes)
        $expireOtp = Carbon::now()->addMinutes(10);




        /*
        |--------------------------------------------------------------------------
        | Logged In Recruiter
        |--------------------------------------------------------------------------
        */

        if (Auth::guard('recruiter')->check()) {

            $user = Auth::guard('recruiter')->user();

            $user->update([
                'otp'        => $otp,
                'expire_otp' => $expireOtp,
            ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Recruiter By Email
            |--------------------------------------------------------------------------
            */

            $user = Recruiter::where('email', $request->email)->first();

            if (!$user) {

                return response()->json([
                    'status'  => false,
                    'message' => 'Recruiter not found'
                ], 404);
            }

            $user->update([
                'otp'        => $otp,
                'expire_otp' => $expireOtp,
            ]);
        }






        /*
        |--------------------------------------------------------------------------
        | Send OTP
        |--------------------------------------------------------------------------
        */

        if ($request->type == 'phone') {

            $message = "Your verification code is: " . $otp;

            $this->send_otp_to_phone(
                $request->phone,
                $message
            );

        } else {

            Mail::to($user->email)->send(
                new SendOtpMail($otp)
            );
        }






        return response()->json([
            'status'  => true,
            'message' => 'OTP sent successfully'
        ]);

    } catch (\Exception $e) {

        \Log::error('Send OTP Error: ' . $e->getMessage());

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage()
        ], 500);
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
            $data       = Recruiter::where('email', $request->email)->first();
            $expiryDate = date('Y-m-d H:i:s', strtotime($data->expire_otp));
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
            $data       = Recruiter::where('email', $request->email)->update(['password'  => Hash::make($request->password)]);
            if ($data)
                return $this->customSuccessRes('Password Change Successfully');
            else
                return $this->customErrorRes('Password Change Successfully');
        } catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * viewRecruiter
     *
     * @param  mixed $request
     * @return void
     */
    public function viewRecruiter(ChangePasswordRequest $request)
    {
        try {
            $data       = Recruiter::where('email', $request->email)->update(['password'  => Hash::make($request->password)]);
            if ($data)
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
            $data       = Recruiter::find(auth()->user()->id);
            if ($data)
                return $this->recordFoundWithResponse(new RecruiterResource($data));
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

    // public function updateProfile(ProfileRequest $request)
    // {
    //     try {
    //         $data       = Recruiter::find(auth()->user()->id);
    //         if ($data) {
    //             $data->update($request->requestData());
    //             Clinic::updateOrCreate(['recruiter_id' => auth()->guard('recruiter')->user()->id], $request->clinicData());
    //             $data->dentistryPractices()->sync($request->dentistry);
    //             $data->lookingFor()->sync($request->looking);
    //             // $data->RoleInPractice()->sync($request->practice_role);
    //             $data->useSoftware()->sync($request->use_software);
    //             return $this->customSuccessRes('Profile Updated Successfully');
    //         } else
    //             return $this->customErrorRes('Somthing Went Wrong!');
    //     } catch (\Exception $e) {
    //         return $this->getExceptionResponse($e);
    //     }
    // }

//   public function updateProfile(ProfileRequest $request)
//     {
//         try {
//             $data       = Recruiter::find(auth()->user()->id);
//             if($data){
//                 $data->update($request->requestData());
//                 Clinic::updateOrCreate(['recruiter_id' => auth()->guard('recruiter')->user()->id],$request->clinicData());
//                 $data->dentistryPractices()->sync($request->dentistry);
//                 $data->lookingFor()->sync($request->looking);
//                 // $data->RoleInPractice()->sync($request->practice_role);
//                 $data->useSoftware()->sync($request->use_software);
//                 return $this->customSuccessRes('Profile Updated Successfully');
//             }
//             else
//                 return $this->customErrorRes('Somthing Went Wrong!');
//         } catch (\Exception $e) {
//             return $this->getExceptionResponse($e);
//         }
//     }

public function updateProfile(ProfileRequest $request)
{
    try {

        $recruiter = Recruiter::find(
            auth()->guard('recruiter')->id()
        );

        if (!$recruiter) {

            return response()->json([
                'status' => false,
                'message' => 'Recruiter not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE RECRUITER
        |--------------------------------------------------------------------------
        */

        $recruiter->update(
            $request->requestData()
        );

        /*
        |--------------------------------------------------------------------------
        | UPDATE CLINIC
        |--------------------------------------------------------------------------
        */

        Clinic::updateOrCreate(
            [
                'recruiter_id' => $recruiter->id
            ],
            $request->clinicData()
        );

        /*
        |--------------------------------------------------------------------------
        | SYNC RELATIONS
        |--------------------------------------------------------------------------
        */

        $recruiter->dentistryPractices()->sync(
            $request->dentistry ?? []
        );

        $recruiter->lookingFor()->sync(
            $request->looking ?? []
        );

        $recruiter->useSoftware()->sync(
            $request->use_software ?? []
        );

        /*
        |--------------------------------------------------------------------------
        | REFRESH RELATIONS
        |--------------------------------------------------------------------------
        */

        $recruiter->load([
            'clinic',
            'review',
            'lookingFor',
            'dentistryPractices',
            'useSoftware',
    'branches',
        ]);

        return response()->json([

            'status' => true,

            'message' => 'Profile Updated Successfully',

            'data' => new RecruiterResource($recruiter)

        ], 200);

    } catch (\Exception $e) {

        return response()->json([

            'status' => false,

            'message' => $e->getMessage(),

            'line' => $e->getLine(),

            'file' => $e->getFile()

        ], 500);
    }
}

  
}
