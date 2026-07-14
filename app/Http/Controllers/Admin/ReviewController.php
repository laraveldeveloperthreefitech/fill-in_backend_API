<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{ClinicReview,CandidateReview};

class ReviewController extends Controller
{
    // public function candidate(Request $request){
    //     try{
    //         $data = CandidateReview::with('clinic','candidate')->search($request)->Paginate(10);
    //         return view('admin.review.candidate.index',compact('data'));
    //     }catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return redirect()->back()->with('error', 'Something Went wrong');
    //     }
    // }
    
    
    public function candidate(Request $request)
{ 
    try {
        $query = CandidateReview::with(['clinic','candidate']);

        // 🔍 SEARCH FILTER
        if (!empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%$search%")
                  ->orWhereHas('clinic', function ($q1) use ($search) {
                      $q1->where('name', 'like', "%$search%");
                  })
                  ->orWhereHas('candidate', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%");
                  });
            });
        }

        // 🔥 MAIN FIX (IMPORTANT)
        $data = $query->latest()->paginate(10)->appends($request->all());

        return view('admin.review.candidate.index', compact('data'));

    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return redirect()->back()->with('error', 'Something Went wrong');
    }
}


    public function candidatedelete(Request $request){
        try{
            $data = CandidateReview::where('id',$request->id)->delete();
            if($data)
                return  response()->json(['status' => true,]);
            else
                return  response()->json(['status' => false]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return  response()->json(['status' => false]);
        }
    }

    // public function clinic(Request $request){
    //     try{
    //         $data = ClinicReview::with('clinic','candidate')->search($request)->Paginate(10);
    //         return view('admin.review.clinic.index',compact('data'));
    //     }catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return redirect()->back()->with('error', 'Something Went wrong');
    //     }
    // }
    
    public function clinic(Request $request)
{
    try {
        $data = ClinicReview::with(['clinic','candidate'])
                    ->search($request)
                    ->latest()
                    ->paginate(10)
                    ->appends($request->all());

        return view('admin.review.clinic.index', compact('data'));

    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return back()->with('error', $e->getMessage());
    }
}


    public function delete(Request $request){
        try{
            $data = ClinicReview::where('id',$request->id)->delete();
            if($data)
                return  response()->json(['status' => true,]);
            else
                return  response()->json(['status' => false]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return  response()->json(['status' => false]);
        }
    }
}
