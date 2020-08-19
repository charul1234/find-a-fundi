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
        'booking_id','user_id','date','start_time','end_time','service_title','requirements','price','status','is_verify','verified_by'
    ];
}
