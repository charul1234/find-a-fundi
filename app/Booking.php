<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'user_id', 'title','description','location','latitude','longitude','budget','is_rfq','request_for_quote_budget','is_hourly','estimated_hours','min_budget','max_budget','is_package','quantity','datetime','service_datetime','requirement','status','requested_id'

    ];
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
        return $this->belongsTo(BookingUser::class,'booking_id');
    }
}
