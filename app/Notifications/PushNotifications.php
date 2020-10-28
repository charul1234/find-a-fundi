<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Edujugon\PushNotification\PushNotification;
use Edujugon\PushNotification\Messages\PushMessage;
use Edujugon\PushNotification\Channels\ApnChannel;
use Edujugon\PushNotification\Channels\FcmChannel;

class PushNotifications extends Notification
{
    use Queueable;

    public $push_data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($push_data)
    {
        $this->push_data = $push_data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if($notifiable->device_type==config('constants.DEVICE_TYPE_IOS')){            
            return [ApnChannel::class];            
        }else{
            return [FcmChannel::class];
        }
    }

    /**
     * Get the APN representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toApn($notifiable)
    {
        $title=isset($this->push_data['subject'])?$this->push_data['subject']:'';
        $push_message=isset($this->push_data['message'])?$this->push_data['message']:'';

        $response = (new PushMessage)
                    ->title($title)
                    ->body($push_message)
                    ->extra($this->push_data)
                    ->sound('default')
                    ->badge(0);

        return $response;
    }

    /**
     * Get the FCM representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toFcm($notifiable)
    {
        $title=isset($this->push_data['subject'])?$this->push_data['subject']:'';
        $push_message=isset($this->push_data['message'])?$this->push_data['message']:'';

        $response = (new PushMessage)
                    // ->title($title)
                    // ->body($push_message)
                    ->extra($this->push_data)
                    ->sound('default')
                    ->badge(0);

        return $response;
    }
    
}
