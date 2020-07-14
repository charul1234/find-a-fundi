<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'city_id', 'experience_level_id', 'payment_option_id', 'dob', 'facebook_url', 'twitter_url', 'linkedin_url', 'googleplus_url', 'instagram_url', 'work_address', 'radius', 'latitude', 'longitude', 'passport_number', 'fundi_is_middlemen', 'fundi_have_tools', 'fundi_have_smartphone', 'display_seeker_reviews', 'additional_work', 'is_rfq', 'is_package', 'is_hourly', 'price', 'start_time', 'end_time', 'residential_address'];

    // For get user
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    /**
     * Get the experience level information that belong to this user.
    */
    public function experience_level()
    {
        return $this->belongsTo(ExperienceLevel::class,'experience_level_id');
    }
    /**
     * Get the Payment information that belong to this user.
    */
    public function payment_option()
    {
        return $this->belongsTo(PaymentOption::class,'payment_option_id');
    }
    
}