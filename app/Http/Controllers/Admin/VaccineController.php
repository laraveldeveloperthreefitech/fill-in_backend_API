<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vaccination;
use App\Http\Requests\Admin\EmploymentRequest;

class VaccineController extends Controller
{
    /**
     * index list vaccination
     *
     * @return void
     */
    public function index(Request $request){
        try{
            $data =Vaccination::search($request)->Paginate(10);
            return view('admin.vaccination.index',compact('data'));
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * changeStatus vaccination
     *
     * @param  mixed $request
     * @return void
     */
    public function changeStatus(Request $request){
        try{
            $data =Vaccination::find($request->id);
            if($data){
                $data->update([
                    'status' => $data->status ? 0 : 1
                ]);
             return redirect()->route('admin.vaccination')->with('success','status change successfully');
           }
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * activeAll vaccination
     *
     * @param  mixed $request
     * @return void
     */
    public function activeAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);
                $data = Vaccination::whereIn('id',$ids)->update(['status' => 1]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * deActiveAll vaccination
     *
     * @param  mixed $request
     * @return void
     */
    public function deActiveAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = Vaccination::whereIn('id',$ids)->update(['status' => 0]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * update vaccination
     *
     * @param  mixed $request
     * @return void
     */
    public function updateOrAdd(EmploymentRequest $request){
        try{
            $data = Vaccination::updateOrCreate(['id' => $request->id],$request->requestData());
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
            $data =Vaccination::where('id',$request->id)->delete();
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
