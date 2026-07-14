<?php

namespace App\Services;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\{User,Candidate,Recruiter};
use App\Notifications\AdminFCMNotification;
use App\Notifications\RecruiterFCMNotification;
use App\Notifications\CandidateFCMNotification;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\FirebaseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Create a new class instance.
     */
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/fill-in-test-a3fc110900ca.json'));

        $this->messaging = $factory->createMessaging();
    }

    public function sendToToken($fcmToken, $title, $body, $type = null, $id = null, $icon = null, $imageType = null)
    {
        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withNotification(Notification::create($title, $body))
            ->withData([
                'type' => $type,
                'id' => $id,
                'icon' => $icon,
                'name' => $imageType
            ]);

        try {
            return $this->messaging->send($message);
        } catch (NotFound $e) {
            // Specifically handles 'Requested entity was not found'
            Log::warning('FCM Token Not Found: ' . $e->getMessage());
            $this->deleteInvalidToken($fcmToken);
        } catch (MessagingException $e) {
            // Handles messaging-specific exceptions like 'InvalidArgument', 'Unregistered'
            Log::warning('FCM Messaging Error: ' . $e->getMessage());

            if (
                str_contains($e->getMessage(), 'Unregistered') ||
                str_contains($e->getMessage(), 'InvalidArgument') ||
                str_contains($e->getMessage(), 'not a valid FCM registration token')
            ) {
                $this->deleteInvalidToken($fcmToken);
            }
        } catch (FirebaseException $e) {
            Log::error('General Firebase Error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Unexpected Error Sending FCM: ' . $e->getMessage());
        }
    }

    private function deleteInvalidToken($fcmToken)
    {
        $deleted = DB::table('recruiter_f_c_m_tokens')->where('fcm_token', $fcmToken)->delete();

        if (!$deleted) {
            DB::table('candidate_f_c_m_tokens')->where('fcm_token', $fcmToken)->delete();
        }

        Log::info("Deleted invalid FCM token from DB: $fcmToken");
    }


   public function notifyAdmins($message, $title, $redirectUrl = null)
    {
        $admins = User::all();
        foreach ($admins as $admin) {
            // Save to DB
            $admin->notify(new AdminFCMNotification($message, $title, $redirectUrl));

            // Send FCM
            foreach ($admin->AdminFCM as $fcm) {
                $this->sendToToken($fcm->fcm_token, $message, $title);
            }
        }
    }

    public function notifyCandidates($ids, $message, $title, $route ,$icon, $type, $id, $imageType, $redirectUrl = null)
    {
        if(is_array($ids)){
            $candidates = Candidate::whereIn('id',$ids)->get();
            foreach ($candidates as $candidate) {
                $candidate->notify(new CandidateFCMNotification($message, $title, $redirectUrl,$icon , $type ,$id,$imageType));
                foreach ($candidate->fcmTokens as $fcm) {
                     $this->sendToToken($fcm->fcm_token, $title, $message,$type,$id,$icon,$imageType);
                }
            }
        }else{
             $candidates = Candidate::where('id',$ids)->first();
             if($type != 'chat'){
                 $candidates->notify(new CandidateFCMNotification($message, $title, $redirectUrl,$icon , $type ,$id,$imageType));
             }
            foreach ($candidates->fcmTokens as $fcm) {
                $this->sendToToken($fcm->fcm_token, $title, $message,$type,$id,$icon,$imageType);
            }
        }
        
    }

    public function notifyRecruiters($ids, $message, $title, $route, $icon, $type, $id, $imageType, $redirectUrl = null)

    {
        if(is_array($ids)){
            $recruiters = Recruiter::whereIn('id',$ids)->get();
            foreach ($recruiters as $recruiter) {
                $recruiter->notify(new RecruiterFCMNotification($message, $title, $redirectUrl,$icon , $type ,$id,$imageType));
                foreach ($recruiter->fcmTokens as $fcm) {
                   $this->sendToToken($fcm->fcm_token, $title, $message,$type,$id,$icon,$imageType);
                }
            }
        }else{
             $recruiters = Recruiter::where('id',$ids)->first();
              if($type != 'chat'){
                    $recruiters->notify(new RecruiterFCMNotification($message, $title, $redirectUrl,$icon , $type ,$id,$imageType));
               }
            foreach ($recruiters->fcmTokens as $fcm) {
                $this->sendToToken($fcm->fcm_token, $title, $message,$type,$id,$icon,$imageType);
            }
        }
    }
}
