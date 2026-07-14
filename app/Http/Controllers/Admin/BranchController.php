<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    

    /**
     * Branch List
     */
    public function index(Request $request)
    {
        $branches = Branch::where('recruiter_id', auth()->id())
                    ->select('id','name')
                    ->orderBy('name')
                    ->get();

        return response()->json([
            'status' => true,
            'message' => 'Branch list',
            'data' => $branches
        ]);
    }

    /**
     * Create Branch
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required|string|max:255'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ],422);
        }

        $branch = Branch::create([
            'recruiter_id'=>auth()->id(),
            'name'=>$request->name
        ]);

        return response()->json([
            'status'=>true,
            'message'=>'Branch created successfully',
            'data'=>[
                'id'=>$branch->id,
                'name'=>$branch->name
            ]
        ]);
    }

    /**
     * Update Branch
     */
    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required|string|max:255'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ],422);
        }

        $branch = Branch::where('id',$id)
                    ->where('recruiter_id',auth()->id())
                    ->first();

        if(!$branch){
            return response()->json([
                'status'=>false,
                'message'=>'Branch not found.'
            ],404);
        }

        $branch->update([
            'name'=>$request->name
        ]);

        return response()->json([
            'status'=>true,
            'message'=>'Branch updated successfully.',
            'data'=>[
                'id'=>$branch->id,
                'name'=>$branch->name
            ]
        ]);
    }

    /**
     * Delete Branch
     */
    public function destroy($id)
    {
        $branch = Branch::where('id',$id)
                    ->where('recruiter_id',auth()->id())
                    ->first();

        if(!$branch){
            return response()->json([
                'status'=>false,
                'message'=>'Branch not found.'
            ],404);
        }

        $branch->delete();

        return response()->json([
            'status'=>true,
            'message'=>'Branch deleted successfully.'
        ]);
    }
}
