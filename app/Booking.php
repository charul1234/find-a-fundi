<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Booking extends Model implements HasMedia
{
    use HasMediaTrait;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'user_id', 'title','description','location','latitude','longitude','budget','is_rfq','request_for_quote_budget','is_hourly','estimated_hours','min_budget','max_budget','is_package','quantity','datetime','service_datetime','requirement','status','requested_id','is_quoted'

    ];
    public function registerMediaCollections(){
        $this->addMediaCollection('booking_works_photo')
        ->useFallbackUrl(asset(config('constants.NO_IMAGE_URL')))
        ->useFallbackPath(public_path(config('constants.NO_IMAGE_URL')));
             //->singleFile();     
    } 
    /**
     * Get the category information that belong to this booking.
    */
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    /**
     * Get the subcategory information that belong to this booking_id
    */
    public function subcategory()
    {
        return $this->hasMany(BookingSubcategory::class,'booking_id');
    }
    /**
     * Get the user information that belong to this user_id.
    */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    /**
     * Get the booking information that belong to this user_id.
    */
    public function booking_user()
    {
        return $this->hasMany(BookingUser::class,'booking_id','id');
    }
}
