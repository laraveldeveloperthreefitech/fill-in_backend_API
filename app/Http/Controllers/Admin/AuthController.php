<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Clinic,Candidate,Recruiter,JobListing};
use Illuminate\Support\Facades\{Auth,Hash,Cookie,Session};
use App\Models\AdminFcmToken;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\TimezoneHelper;

class AuthController extends Controller
{
    public function home(){
        $today = date('Y-m-d');
        $card = [
            'Total Candidate'       => Candidate::count(),
            'Active Candidate'      => Candidate::where('status', 1)->count(),
            'Total Recruiter'       => Recruiter::count(),
            'Verified Recruiter'    => Recruiter::whereHas('clinic')->where('status', 1)->count(),
            'Total Jobs'            => JobListing::count(),
            'Active Jobs'           => JobListing::where('status', 1)->where('expire_date', '>', $today)->count(),
        ];
        $data = JobListing::withCount('candidates')->with('candidates')
                ->where('created_at', '>=', Carbon::now()->subDays(30)) // Get jobs from the last 30 days
                ->orderByDesc('candidates_count')
                ->latest() // Sort by latest created_at
                ->limit(10)
                ->get();
       
        return view('admin.index',compact('card','data'));
    }

    public function login(){
        return view('admin.login');
    }

    public function auth(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        try{
           
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                if ($request->has('remember')) {
                    Cookie::queue('email', $request->email, 1440);
                    Cookie::queue('password', $request->password, 1440);
                } else {
                    Cookie::queue('email', "", 1440);
                    Cookie::queue('password', "", 1440);
                }
                $request->session()->regenerate();
                $request->session()->save(); // 🔐 This saves it into sessions table
            
                $sessionId = session()->getId(); // Now it's safely stored in DB
              if ($request->filled('fcm_token')) {
                    AdminFcmToken::updateOrCreate(
                        ['fcm_token' => $request->fcm_token],
                        [
                            'user_id'    => auth()->id(),
                            'session_id' => $sessionId,
                        ]
                    );
                }             
                return redirect()->route('admin.home');
            }
            return redirect()->back()->with('error', 'invalid credantials');
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }

    public function profile(Request $request){
        try{
            return view('admin.profile');
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    public function Update(Request $request){
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
        ]);
        try{
            $user = User::find(auth()->user()->id)->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
            return redirect()->back()->with('success','profile successfully updated');
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }

    public function changePassword(Request $request)
    {
        // Validate request
        $request->validate([
            'old_password'      => 'required',
            'new_password'      => 'required|min:6',
            'confirm_password'  => 'required|same:new_password',
        ]);
        try{
            $user = Auth::user();
            // Check if the old password is correct
            if (!Hash::check($request->old_password, $user->password)) {
                return back()->with('error', 'Old password is incorrect.');
            }
            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return back()->with('success', 'Password changed successfully.');
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }

    public function logout(Request $request)
    {
        try{
            Auth::logout(); // Logout the user
            $sessionId = session()->getId();
            AdminFcmToken::where('session_id',$sessionId)->delete();
            return redirect()->route('login')->with('success', 'Logged out successfully.');
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    public function updateTimezone(Request $request){
        session(['user_timezone' => $request->timezone]);
    }
}
