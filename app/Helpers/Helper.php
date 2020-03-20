<?php

use Illuminate\Support\Facades\Cache;

use App\User;
use App\Setting;

/**
* Method Name : setSetting 
* Parameter : $option_name,$option_value
* This is using for set setting option_value 
*/

function setSetting($option_name,$option_value){
    $setting=Setting::where(array('option_name'=>$option_name))->first();
    if ($setting!=NULL) {
        $setting->option_value = $option_value;
        $setting->save();
    }else{
        $setting = new Setting;
        $setting->option_name = $option_name;
        $setting->option_value = $option_value;
        $setting->save();
    }
    return true;
} 

/**
* Method Name : getSetting 
* Parameter : $option_name
* This is using for return setting option_value 
*/

function getSetting($option_name){
    if (isset($option_name) && $option_name!='') {        
        $setting = Cache::rememberForever('app_settings', function () {
            return Setting::get();
        });

        $setting=$setting->where('option_name', $option_name)->first();
        return isset($setting->option_value)?$setting->option_value:'';
    }
    return '';
}
?>