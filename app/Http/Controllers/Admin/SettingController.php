<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Http\Traits\{HelperTrait,RestResponse};

class SettingController extends Controller
{
    use RestResponse,HelperTrait;

    public function form()
    {
        $setting = Setting::first(); 
        return view('admin.setting.form', compact('setting'));
    }

    // public function store(Request $request)
    //     {
    //         $validated = $request->validate([
    //             'about_us' => 'required',
    //             'privacy_policy' => 'required',
    //             'email' => 'required|email',
    //             'phone' => 'required',
    //             'facebook' => 'nullable',
    //             'twitter' => 'nullable',
    //             'instagram' => 'nullable',
    //             'linkedin' => 'nullable',
    //             'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         ]);
        
    //         $setting = Setting::firstOrNew([]);
    //         $setting->fill($validated);
        
    //         if ($request->hasFile('logo')) {
    //             $image = $this->imageUploadToBase64(
    //                 config('filepaths.admin.directory'),
    //                 $request->file('logo')
    //             );
        
    //             $setting->logo = $image;
    //         }
        
    //         $setting->save();
        
    //         return redirect()->back()->with('success', 'Settings updated successfully.');
    //     }
    
    
      public function store(Request $request)
        {
            $validated = $request->validate([
                'about_us' => 'required',
                'privacy_policy' => 'required',
                'terms_conditions' => 'required',
                'email' => 'required|email',
                'phone' => 'required',
                'facebook' => 'nullable',
                'twitter' => 'nullable',
                'instagram' => 'nullable',
                'linkedin' => 'nullable',
                'radius' => 'nullable|numeric|min:1',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $setting = Setting::firstOrNew([]);
            $setting->fill($validated);
        
          if ($request->hasFile('logo')) {

                $file = $request->file('logo');
                $filename = time() . '_' . $file->getClientOriginalName();
            
                $destinationPath = public_path('admin/assets/images');
                $file->move($destinationPath, $filename);
            
                // Save full URL instead of just filename
                $setting->logo = url('admin/assets/images/' . $filename);
            }
        
            $setting->save();
        
            return redirect()->back()->with('success', 'Settings updated successfully.');
        }
  
}
