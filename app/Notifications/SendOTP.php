<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class SendOTP extends Notification
{
    protected $requested_otp;
    protected $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($requested_otp, $token)
    {
        $this->requested_otp = $requested_otp;
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if($this->requested_otp=='forget_password'){            
            return (new MailMessage)
                    ->subject(Lang::get('Reset Password Notification'))
                    ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
                    ->line(Lang::get('Reset password OTP is :otp', ['otp' => $this->token]))
                    ->line(Lang::get('This password reset OTP will expire in :count minutes.', ['count' => config('auth.passwords.users.expire')]))
                    ->line(Lang::get('If you did not request a password reset, no further action is required.'));
        }

        return false;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
