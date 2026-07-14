<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Http\Requests\Admin\clinicRequest;

class ClinicController extends Controller
{
    /**
     * index list clinic
     *
     * @return void
     */
    public function index(Request $request){
        try{
            $data = Clinic::withCount('job')->with('recruiter')->search($request)->Paginate(10);
            return view('admin.clinic.index',compact('data'));
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * changeStatus clinic
     *
     * @param  mixed $request
     * @return void
     */
    public function changeStatus(Request $request){
        try{
            $data = Clinic::find($request->id);
            if($data){
                $data->update([
                    'status' => $data->status ? 0 : 1
                ]);
             return redirect()->route('admin.clinic')->with('success','status change successfully');
           }
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * activeAll clinic
     *
     * @param  mixed $request
     * @return void
     */
    public function activeAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = Clinic::whereIn('id',$ids)->update(['status' => 1]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * deActiveAll clinic
     *
     * @param  mixed $request
     * @return void
     */
    public function deActiveAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = Clinic::whereIn('id',$ids)->update(['status' => 0]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * update clinic
     *
     * @param  mixed $request
     * @return void
     */
    public function update(clinicRequest $request){
        try{
            $data = Clinic::where('id',$request->id)->update($request->requestData());
            if($data)
                return  response()->json(['status' => true,]);
            else
                return  response()->json(['status' => false]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return  response()->json(['status' => false]);
        }
    }

    public function verify($id){
        try{
            $data = Clinic::find($id);
            if($data){
                $data->update([
                    'verification' => 1
                ]);
             return redirect()->route('admin.clinic')->with('success','verified successfully');
           }
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
}
