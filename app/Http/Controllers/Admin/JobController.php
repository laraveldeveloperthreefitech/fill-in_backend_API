<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobListing;
use App\Http\Requests\Admin\jobRequest;

class JobController extends Controller
{
     /**
     * index list job
     *
     * @return void
     */
    // public function index(Request $request){
    //     try{
    //         $data = JobListing::with('clinic')->search($request)->Paginate(10);
    //         return view('admin.job.index',compact('data'));
    //     }catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return redirect()->back()->with('error', 'Something Went wrong');
    //     }
    // }
    
 

public function index(Request $request)
{
    try {
        $data = JobListing::query()
            ->with('clinic')
            ->search($request)
            ->latest() // ✅ ORDER BY created_at DESC
            ->paginate(10)
            ->appends($request->all()); // keep filters

        return view('admin.job.index', compact('data'));

    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong');
    }
}



    public function viewJob($id){
        try{
            $data = JobListing::find($id);
            return view('admin.job.view',compact('data'));
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * changeStatus job
     *
     * @param  mixed $request
     * @return void
     */
    public function changeStatus(Request $request){
        try{
            $data = JobListing::find($request->id);
            if($data){
                $data->update([
                    'status' => $data->status ? 0 : 1
                ]);
             return redirect()->route('admin.job')->with('success','status change successfully');
           }
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * activeAll job
     *
     * @param  mixed $request
     * @return void
     */
    public function activeAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);
                $data = JobListing::whereIn('id',$ids)->update(['status' => 1]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * deActiveAll job
     *
     * @param  mixed $request
     * @return void
     */
    public function deActiveAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = JobListing::whereIn('id',$ids)->update(['status' => 0]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * update job
     *
     * @param  mixed $request
     * @return void
     */
    public function update(jobRequest $request){
        try{
            $data = JobListing::where('id',$request->id)->update($request->requestData());
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
