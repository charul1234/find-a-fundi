<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class BookingUser extends Model implements HasMedia
{
    use HasMediaTrait;
    //protected $table='package_user';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'id','user_id','booking_id','is_rfq','budget','service_datetime','requirement','is_quoted','status','reason'
    ];
     public function registerMediaCollections(){
        $this->addMediaCollection('booking_works_photo')
        ->useFallbackUrl(asset(config('constants.NO_IMAGE_URL')))
        ->useFallbackPath(public_path(config('constants.NO_IMAGE_URL')));
             //->singleFile();     
    } 
    /**
     * Get the user information that belong to this user_id.
    */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
