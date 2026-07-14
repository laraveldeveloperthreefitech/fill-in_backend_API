<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClinicSupport;
use Illuminate\Support\Facades\Mail;

class ClinicSupportController extends Controller
{
    // Show all support requests
    // public function index(Request $request)
    // {
    //     $query = ClinicSupport::with('clinic');

    //     if ($request->has('search')) {
    //         $query->where('email', 'like', '%' . $request->search . '%');
    //     }

    //     $data = $query->orderBy('id', 'desc')->paginate(10);

    //     return view('admin.candidate_support.clinic', compact('data'));
    // }
    
    public function index(Request $request)
{
    $query = ClinicSupport::with('clinic');

    // ✅ FIXED SEARCH (no email column in this table)
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {

            // search in message (this table)
            $q->where('message', 'like', "%$search%")

              // OR search in related clinic table
              ->orWhereHas('clinic', function ($sub) use ($search) {
                  $sub->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
              });
        });
    }

    $data = $query->orderBy('id', 'desc')->paginate(10);

    return view('admin.candidate_support.clinic', compact('data'));
}


    // Handle response submission
    public function respond(Request $request)
    {
        try {
            $request->validate([
                'support_id' => 'required|exists:clinic_support,id',
                'message' => 'required',
            ]);
        
            $support = ClinicSupport::with('clinic')->findOrFail($request->support_id);
        
            // Send email
            Mail::raw($request->message, function ($message) use ($support) {
                $message->to($support->clinic->email)
                        ->subject('Clinic Support Response');
            });
        
            // Update response status
            $support->update([
                'response_status' => 1
            ]);
        
            return redirect()->route('clinic-support.index')->with('success', 'Response sent successfully!');
        } catch (\Exception $e) {
            return redirect()->route('clinic-support.index')->with('error', 'Failed to send response: ' . $e->getMessage());
        }
    }
    
    
}
