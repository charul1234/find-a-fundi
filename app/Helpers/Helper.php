<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\User;
use App\Setting;
use Edujugon\PushNotification\PushNotification;
use App\Notifications\PushNotifications;

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
function sendEmailVerifyToUser($data = null)
{
    if ($data != null) {
        $settings                 = [];
        $settings["subject"]      = "Email Verification";
        $settings['emailType']    = 'Email Verification';
        $settings['from']         = getSetting('email');
        $settings['to']           = $data->email;
        $settings['sender']       = getSetting('contact_person_name');
        $settings['receiver']     = $data->name;
        $settings['txtBody']      = view('emails.emailverify_to_user', $settings)->render();        
        unset($settings['txtBody']);
        sendEmail('emails.emailverify_to_user', $settings);
    }
}
function sendEmail($view = null, $settings = null)
{
    if (!empty($settings) && $view != null) {
        $sent = Mail::send($view, $settings, function ($message) use ($settings) {
            $message->from($settings['from'], $settings['sender']);
            $message->to($settings['to'], $settings['receiver'])->subject($settings['subject']);
        });
    }
} 
function sendIphoneNotifications($title = null, $message = null,$tokens=null)
{ 
  $push = new PushNotification('apn');
  $title=isset($title)?$title:'';
  $message=isset($message)?$message:'';
  if(!empty($tokens))
  {
    $results= $push->setMessage([
            'aps' => [
                'alert' => [
                    'title' => $title,
                    'body' => $message
                ],
                'sound' => 'default',
                'badge' => 2

            ],
            'extraPayLoad' => [
                'custom' => $message,
            ]
        ])
        ->setDevicesToken($tokens)->send();
   return $results;
  }        
}
?>