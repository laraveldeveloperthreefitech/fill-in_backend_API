<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CandidateSupport;
use Illuminate\Support\Facades\Mail;

class CandidateSupportController extends Controller
{
    public function index(Request $request)
    {
        $query = CandidateSupport::with('candidate');

        if ($request->has('search')) {
            $query->whereHas('candidate', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $data = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.candidate_support.candidate', compact('data'));
    }

   
    public function respond(Request $request)
{
    try {
        $request->validate([
            'support_id' => 'required|exists:candidate_supports,id',
            'message' => 'required',
        ]);
        
        $support = CandidateSupport::with('candidate')->findOrFail($request->support_id);
        // dd($support);
    
        Mail::raw($request->message, function ($message) use ($support) {
            $message->to($support->candidate->email)
                    ->subject('Candidate Support Response');
        });
    
        $support->update([
            'response_status' => 1
        ]);
    
        return redirect()->route('candidate-support.index')->with('success', 'Response sent successfully!');
    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return redirect()->route('candidate-support.index')->with('error', 'Failed to send response: ' . $e->getMessage());
    }
}

}
