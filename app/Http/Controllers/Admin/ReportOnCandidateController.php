<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportOnCandidate;
use App\Models\Candidate;
use App\Models\Clinic;
use App\Models\{JobListing,ReportOnRecruiter};
use App\Models\ReportOnJob;

class ReportOnCandidateController extends Controller
{
    // public function index(Request $request)
    // {
    //     try {
    //         $reports = ReportOnCandidate::with('candidate', 'clinic')
    //                     ->search($request)
    //                     ->latest()
    //                     ->paginate(10);
    
    //         return view('admin.reportlist.report_on_candidate', compact('reports'));
    //     } catch (\Exception $e) {
    //         \Log::error('Error in ReportOnCandidateController@index: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Something went wrong while fetching the reports.');
    //     }
    // }
    
    public function index(Request $request)
{
    try {
        $query = ReportOnCandidate::with(['candidate', 'clinic']);

        // 🔍 SEARCH FILTER
        if (!empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhereHas('candidate', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%");
                  })
                  ->orWhereHas('clinic', function ($q3) use ($search) {
                      $q3->where('name', 'like', "%$search%");
                  });
            });
        }

        // 📅 DATE FILTER
        if (!empty($request->date)) {
            $dates = explode(' - ', $request->date);

            if (count($dates) == 2) {
                $start = date('Y-m-d 00:00:00', strtotime($dates[0]));
                $end   = date('Y-m-d 23:59:59', strtotime($dates[1]));

                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        // 🔥 MAIN FIX (PAGINATION WITH FILTERS)
        $reports = $query->latest()->paginate(10)->appends($request->all());

        return view('admin.reportlist.report_on_candidate', compact('reports'));

    } catch (\Exception $e) {
        \Log::error('Error in ReportOnCandidateController@index: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while fetching the reports.');
    }
}
    
       
    


public function destroy(Request $request){
    try{
        $data = ReportOnCandidate::where('id',$request->id)->delete();
        if($data)
            return  response()->json(['status' => true,]);
        else
            return  response()->json(['status' => false]);
    }catch (\Exception $e) {
        \Log::error($e->getMessage());
        return  response()->json(['status' => false]);
    }
}

public function jobReportDelete(Request $request){
    try{
        $data = ReportOnJob::where('id',$request->id)->delete();
        if($data)
            return  response()->json(['status' => true,]);
        else
            return  response()->json(['status' => false]);
    }catch (\Exception $e) {
        \Log::error($e->getMessage());
        return  response()->json(['status' => false]);
    }
}

    // List all job reports
    // public function jobReportIndex(Request $request)
    // {
    //     try {
    //         $reports = ReportOnJob::with(['candidate', 'job'])
    //                     ->search($request) 
    //                     ->latest()
    //                     ->paginate(10);
    
    //         return view('admin.reportlist.report_on_job', compact('reports'));
    //     } catch (\Exception $e) {
    //         \Log::error('Error in jobReportIndex: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Something went wrong while fetching job reports.');
    //     }
    // }
    
  public function jobReportIndex(Request $request)
{
    try {
        $query = ReportOnJob::with(['candidate', 'job']);

        if (!empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhereHas('candidate', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%");
                  })
                  ->orWhereHas('job', function ($q3) use ($search) {
                      $q3->where('title', 'like', "%$search%");
                  });
            });
        }

        if (!empty($request->date)) {
            $dates = explode(' - ', $request->date);

            if (count($dates) == 2) {
                $start = date('Y-m-d 00:00:00', strtotime($dates[0]));
                $end   = date('Y-m-d 23:59:59', strtotime($dates[1]));

                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        // 🔥 IMPORTANT FIX HERE
        $reports = $query->latest()->paginate(10)->appends($request->all());

        return view('admin.reportlist.report_on_job', compact('reports'));

    } catch (\Exception $e) {
        \Log::error('Error in jobReportIndex: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while fetching job reports.');
    }
}

    
    // public function ReportOnRecruiter(Request $request)
    // {
    //     try {
    //         $reports = ReportOnRecruiter::with('candidate', 'recruiter')
    //                     ->search($request)
    //                     ->latest()
    //                     ->paginate(10);
    
    //         return view('admin.reportlist.report_on_recruiter', compact('reports'));
    //     } catch (\Exception $e) {
    //         \Log::error('Error in' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Something went wrong while fetching the reports.');
    //     }
    // }
    
    public function ReportOnRecruiter(Request $request)
{
    try {
        $query = ReportOnRecruiter::with(['candidate', 'recruiter.clinic']);

        // 🔍 SEARCH FILTER
        if (!empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhereHas('candidate', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%");
                  })
                  ->orWhereHas('recruiter.clinic', function ($q3) use ($search) {
                      $q3->where('name', 'like', "%$search%");
                  });
            });
        }

        // 📅 DATE FILTER
        if (!empty($request->date)) {
            $dates = explode(' - ', $request->date);

            if (count($dates) == 2) {
                $start = date('Y-m-d 00:00:00', strtotime($dates[0]));
                $end   = date('Y-m-d 23:59:59', strtotime($dates[1]));

                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        // 🔥 MAIN FIX
        $reports = $query->latest()->paginate(10)->appends($request->all());

        return view('admin.reportlist.report_on_recruiter', compact('reports'));

    } catch (\Exception $e) {
        \Log::error('Error in ReportOnRecruiter: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while fetching the reports.');
    }
}
    
    public function deleteReportRecruiter(Request $request)
    {
        try{
            $data = ReportOnRecruiter::where('id',$request->id)->delete();
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


