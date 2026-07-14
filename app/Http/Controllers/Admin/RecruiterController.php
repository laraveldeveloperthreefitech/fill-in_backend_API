<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Recruiter;
use App\Http\Requests\Admin\RecruiterRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\HelperTrait;

/**
 * RecruiterController
 */
class RecruiterController extends Controller
{    
    use HelperTrait;
    /**
     * index list Recruiter
     *
     * @return void
     */
    // public function index(Request $request){
    //     try{
    //         $data = Recruiter::search($request)->Paginate(10);
    //         return view('admin.recruiter.index',compact('data'));
    //     }catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return redirect()->back()->with('error', 'Something Went wrong');
    //     }
    // }
    
   public function index(Request $request)
{
    try {
        $data = Recruiter::query()
            ->search($request)
            ->latest()
            ->paginate(10);

        return view('admin.recruiter.index', compact('data'));

    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong');
    }
}
    
    public function edit(Request $request)
    {
        $data = Recruiter::findOrFail($request->id);
        return view('admin.recruiter.edit', compact('data'));
    }

     public function viewRecruiter($id){
        try{
            $data = Recruiter::find($id);
            return view('admin.recruiter.view',compact('data'));
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    /**
     * changeStatus Recruiter
     *
     * @param  mixed $request
     * @return void
     */
    public function changeStatus(Request $request){
        try{
            $data = Recruiter::find($request->id);
            if($data){
                $data->update([
                    'status' => $data->status ? 0 : 1
                ]);
             return redirect()->route('admin.recuirter')->with('success','status change successfully');
           }
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * activeAll Recruiter
     *
     * @param  mixed $request
     * @return void
     */
    public function activeAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = Recruiter::whereIn('id',$ids)->update(['status' => 1]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * deActiveAll Recruiter
     *
     * @param  mixed $request
     * @return void
     */
    // public function deActiveAll(Request $request){
    //     try{
    //         dd(config('filesystems.default'));
    //         $ids = $request->ids ? $request->ids : [];
    //         if(count($ids) == 0)
    //             return  response()->json(['status' => false,]);

    //             $data = Recruiter::whereIn('id',$ids)->update(['status' => 0]);
    //         if($data)
    //             return  response()->json(['status' => true,]);
    //     }catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return redirect()->back()->with('error', 'Something Went wrong');
    //     }
    // }
    
    public function deActiveAll(Request $request)
{
    try {
        $ids = $request->ids ?? [];

        if (empty($ids)) {
            return response()->json([
                'status' => false,
                'message' => 'First choose the Recruiter.'
            ]);
        }

        $updated = Recruiter::whereIn('id', $ids)->update(['status' => 0]);

        if ($updated) {
            return response()->json([
                'status' => true,
                'message' => 'Selected recruiters deactivated successfully.'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No recruiters were updated.'
        ]);

    } catch (\Exception $e) {
        \Log::error($e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong'
        ]);
    }
}
    
    /**
     * update Recruiter
     *
     * @param  mixed $request
     * @return void
     */
    // public function update(RecruiterRequest $request){
    //     try{
    //         $data = Recruiter::where('id',$request->id)->update($request->requestData());
    //         if($data)
    //             return  redirect()->route('admin.recuirter')->with('success','recuruiter successfully updated!');
    //         else
    //         return  redirect()->back()->with('error','somthing went wrong!');
    //     }catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return  response()->json(['status' => false]);
    //     }
    // }
    
public function update(RecruiterRequest $request)
{
    try {

        $recruiter = Recruiter::find($request->id);

        if (!$recruiter) {

            return redirect()->back()
                ->with('error', 'Recruiter not found!');
        }

        $recruiter->update(
            $request->requestData()
        );

        return redirect()
            ->route('admin.recuirter')
            ->with('success', 'Recruiter successfully updated!');

    } catch (\Exception $e) {

        \Log::error($e->getMessage());

        return redirect()->back()
            ->with('error', $e->getMessage());
    }
}


}
