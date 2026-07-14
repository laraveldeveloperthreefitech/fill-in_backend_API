<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PracticeRole;
use App\Http\Requests\Admin\EmploymentRequest;

class DepartmentController extends Controller
{
     /**
     * index list specialization
     *
     * @return void
     */
    public function index(Request $request){
        try{
            $data = PracticeRole::search($request)->Paginate(10);
            return view('admin.department.index',compact('data'));
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * changeStatus specialization
     *
     * @param  mixed $request
     * @return void
     */
    public function changeStatus(Request $request){
        try{
            $data = PracticeRole::find($request->id);
            if($data){
                $data->update([
                    'status' => $data->status ? 0 : 1
                ]);
             return redirect()->route('admin.specialization')->with('success','status change successfully');
           }
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * activeAll specialization
     *
     * @param  mixed $request
     * @return void
     */
    public function activeAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = PracticeRole::whereIn('id',$ids)->update(['status' => 1]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * deActiveAll specialization
     *
     * @param  mixed $request
     * @return void
     */
    public function deActiveAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = PracticeRole::whereIn('id',$ids)->update(['status' => 0]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * update specialization
     *
     * @param  mixed $request
     * @return void
     */
    public function updateOrAdd(EmploymentRequest $request){
        try{
            $data = PracticeRole::updateOrCreate(['id' => $request->id],$request->requestData());
            if($data)
                return  response()->json(['status' => true,]);
            else
                return  response()->json(['status' => false]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return  response()->json(['status' => false]);
        }
    }

    public function delete(Request $request){
        try{
            $data = PracticeRole::where('id',$request->id)->delete();
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
