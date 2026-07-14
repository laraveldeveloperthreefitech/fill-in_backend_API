<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Recruiter\{CreateClinicRequest};
use App\Models\Clinic;
use App\Http\Traits\RestResponse;
use App\Http\Resources\Recruiter\{ViewClinicResource};

class RecruiterClinicController extends Controller
{    
    use RestResponse;
    /**
     * createClinic
     * Developer : Faizan khan
     * @param  mixed $request
     * @return void
     */
    public function createClinic(CreateClinicRequest $request){
        try{
            $clinic = Clinic::where('recruiter_id',$request->user()->id)->first();
            if($clinic && !$request->id){
                return $this->customErrorRes('clinic already created with the name of' . $clinic->name);
            }
            $id   = ['id' => $request->id ? $request->id : null];
            $data = Clinic::updateOrCreate($id,$request->requestData());
            if($data){
                return $this->newRecordSaveResponse(new ViewClinicResource($data));
            }  
            else
                return $this->customErrorRes('Somthing went wrong.Please try again!');
        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }

    public function viewClinic(Request $request){
        try{ 
            $clincId = $request->user()->clinic ? $request->user()->clinic->id : null;
            $data = Clinic::find($clincId);
            if($data)
                return $this->recordFoundWithResponse(new ViewClinicResource($data));
            else
                return $this->recordNotFoundResponse();

        }catch (\Exception $e) {
            return $this->getExceptionResponse($e);
        }
    }
}
