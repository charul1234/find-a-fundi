<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'booking_id','user_id','date','start_time','end_time','service_title','requirements','price','status','is_verify','is_complete','verified_by','otp'
    ];

     
    /**
     * Get the booking information that belong to this booking.
    */
    public function booking()
    {
        return $this->belongsTo(Booking::class,'booking_id');
    }
}
