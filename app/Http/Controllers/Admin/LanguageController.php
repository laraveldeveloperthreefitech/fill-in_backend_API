<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use App\Http\Requests\Admin\EmploymentRequest;

class LanguageController extends Controller
{
    /**
     * index list language
     *
     * @return void
     */
    public function index(Request $request){
        try{
            $data = Language::search($request)->Paginate(10);
            return view('admin.language.index',compact('data'));
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * changeStatus language
     *
     * @param  mixed $request
     * @return void
     */
    public function changeStatus(Request $request){
        try{
            $data = Language::find($request->id);
            if($data){
                $data->update([
                    'status' => $data->status ? 0 : 1
                ]);
             return redirect()->route('admin.language')->with('success','status change successfully');
           }
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * activeAll language
     *
     * @param  mixed $request
     * @return void
     */
    public function activeAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = Language::whereIn('id',$ids)->update(['status' => 1]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * deActiveAll language
     *
     * @param  mixed $request
     * @return void
     */
    public function deActiveAll(Request $request){
        try{
            $ids = $request->ids ? $request->ids : [];
            if(count($ids) == 0)
                return  response()->json(['status' => false,]);

                $data = Language::whereIn('id',$ids)->update(['status' => 0]);
            if($data)
                return  response()->json(['status' => true,]);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something Went wrong');
        }
    }
    
    /**
     * update language
     *
     * @param  mixed $request
     * @return void
     */
    public function updateOrAdd(EmploymentRequest $request){
        try{
            $data = Language::updateOrCreate(['id' => $request->id],$request->requestData());
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
            $data = Language::where('id',$request->id)->delete();
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
