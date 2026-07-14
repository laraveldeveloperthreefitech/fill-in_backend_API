<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Http\Requests\Admin\CandidateRequest;
use App\Http\Traits\HelperTrait;

class CandidateController extends Controller
{
    use HelperTrait;
    /**
     * index list Candidate
     *
     * @return void
     */
    // public function index(Request $request){
    //     try{
    //         $data = Candidate::withCount('job')->with('job')->search($request)->Paginate(10);
    //         return view('admin.candidate.index',compact('data'));
    //     }catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return redirect()->back()->with('error', 'Something Went wrong');
    //     }
    // }
    
    public function index(Request $request)
{
    try {

        $data = Candidate::query()
            ->withCount('job')
            ->with('job')
            ->search($request)
            ->latest() // optional but recommended
            ->paginate(10);

        return view('admin.candidate.index', compact('data'));

    } catch (\Exception $e) {

        \Log::error($e->getMessage());

        return redirect()
            ->back()
            ->with('error', 'Something went wrong');
    }
}
    
    /**
     * changeStatus Candidate
     *
     * @param  mixed $request
     * @return void
     */
    public function changeStatus(Request $request){
        try{
            $data = Candidate::find($request->id);
            if($data){
                $data->update([
                    'status' => $data->status ? 0 : 1
                ]);
             return redirect()->route('admin.candidate')->with('success','status change successfully');
           }
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * activeAll Candidate
     *
     * @param  mixed $request
     * @return void
     */
    public function activeAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = Candidate::whereIn('id',$ids)->update(['status' => 1]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * deActiveAll Candidate
     *
     * @param  mixed $request
     * @return void
     */
    public function deActiveAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = Candidate::whereIn('id',$ids)->update(['status' => 0]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    public function edit(Request $request)
    {
        $data = Candidate::findOrFail($request->id);
        return view('admin.candidate.edit', compact('data'));
    }

    public function view(Request $request)
    {
        $data = Candidate::findOrFail($request->id);
        return view('admin.candidate.view', compact('data'));
    }
    /**
     * update Candidate
     *
     * @param  mixed $request
     * @return void
     */
    // public function update(CandidateRequest $request){
    //     try{
    //         $data = Candidate::where('id',$request->id)->update($request->requestData());
    //         if($data)
    //             return  redirect()->route('admin.candidate')->with('success','Candidate successfully updated!');
    //         else
    //             return  redirect()->back()->with('error','somthing went wrong!');
    //     }catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return  response()->json(['status' => false]);
    //     }
    // }
    
 public function update(CandidateRequest $request)
{
    try {

        $candidate = Candidate::find($request->id);

        if (!$candidate) {

            return redirect()->back()
                ->with('error', 'Candidate not found!');
        }

        $candidate->update(
            $request->requestData()
        );

        return redirect()
            ->route('admin.candidate')
            ->with('success', 'Candidate successfully updated!');

    } catch (\Exception $e) {

        \Log::error($e->getMessage());

        return redirect()->back()
            ->with('error', $e->getMessage());
    }
}

}
