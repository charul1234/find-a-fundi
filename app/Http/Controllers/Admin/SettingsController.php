<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

use Form;

use App\Setting;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.setting.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'email'               => 'required|email',
            'contact_number'      => 'required',
            'address'             => 'required',
            'logo'                => 'image',
            'footer_logo'         => 'image',
            'favicon'             => 'file|mimes:jpeg,png,jpg,ico',
            'facebook_url'        => 'nullable|url|active_url',
            'instagram_url'      => 'nullable|url|active_url',
            'twitter_url'         => 'nullable|url|active_url',
            'linkedin_url'        => 'nullable|url|active_url',
        ];

        $request->validate($rules, [], []);
        
        $data = array(
                        array('option_name'=>'email','option_value'=>$request->email),
                        array('option_name'=>'contact_number','option_value'=>$request->contact_number),
                        array('option_name'=>'address','option_value'=>$request->address),
                        array('option_name'=>'facebook_url','option_value'=>$request->facebook_url),
                        array('option_name'=>'instagram_url','option_value'=>$request->instagram_url),
                        array('option_name'=>'twitter_url','option_value'=>$request->twitter_url),
                        array('option_name'=>'linkedin_url','option_value'=>$request->linkedin_url),
                        array('option_name'=>'logo','option_value'=>$request->logo),
                        array('option_name'=>'footer_logo','option_value'=>$request->footer_logo),
                        array('option_name'=>'favicon','option_value'=>$request->favicon)
                    );
        if (!empty($data)) {
            foreach ($data as $row) {
                $is_file = FALSE;
                if ($row['option_name']=='logo') {
                    $is_file = TRUE;
                    if ($request->hasFile('logo')){
                        $file = $request->file('logo');
                        $customimagename  = time() . 'logo.' . $file->getClientOriginalExtension();
                        $file->storeAs(config('constants.SETTING_IMAGE_URL'), $customimagename);
                        $setting=Setting::where(array('option_name'=>$row['option_name']))->first();
                        if (isset($setting->option_value) && $setting->option_value!='' && \Storage::exists(config('constants.SETTING_IMAGE_URL').$setting->option_value)) {
                            \Storage::delete(config('constants.SETTING_IMAGE_URL').$setting->option_value);
                        }
                        $row['option_value'] = $customimagename;
                    } 
                }if ($row['option_name']=='footer_logo') {
                    $is_file = TRUE;
                    if ($request->hasFile('footer_logo')){
                        $file = $request->file('footer_logo');
                        $customimagename  = time() . 'footer_logo.' . $file->getClientOriginalExtension();
                        $file->storeAs(config('constants.SETTING_IMAGE_URL'), $customimagename);
                        $setting=Setting::where(array('option_name'=>$row['option_name']))->first();
                        if (isset($setting->option_value) && $setting->option_value!='' && \Storage::exists(config('constants.SETTING_IMAGE_URL').$setting->option_value)) {
                            \Storage::delete(config('constants.SETTING_IMAGE_URL').$setting->option_value);
                        }
                        $row['option_value'] = $customimagename;
                    } 
                }else if ($row['option_name']=='favicon') {
                    $is_file = TRUE;
                    if ($file = $request->hasFile('favicon')){
                        $file = $request->file('favicon');
                        $customimagename  = time() . 'favicon.' . $file->getClientOriginalExtension();
                        $file->storeAs(config('constants.SETTING_IMAGE_URL'), $customimagename);
                        $setting=Setting::where(array('option_name'=>$row['option_name']))->first();
                        if (isset($setting->option_value) && $setting->option_value!='' && \Storage::exists(config('constants.SETTING_IMAGE_URL').$setting->option_value)) {
                            \Storage::delete(config('constants.SETTING_IMAGE_URL').$setting->option_value);
                        }
                        $row['option_value'] = $customimagename;
                    }
                }
                if (isset($row['option_value']) && $row['option_value']!='') {
                    setSetting($row['option_name'],$row['option_value']);
                }else{
                    if ($is_file == FALSE) {
                        Setting::where('option_name',$row['option_name'])->delete();
                    }    
                }
            }
        }

        // For set setting in cache
        Cache::put('app_settings', Setting::get());
        
        $request->session()->flash('success',__('global.messages.update'));
        return redirect()->route('admin.settings.index');
    }
}