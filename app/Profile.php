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
        'user_id', 'city_id', 'experience_level_id', 'payment_option_id', 'dob', 'facebook_url', 'twitter_url', 'linkedin_url', 'googleplus_url', 'instagram_url', 'work_address', 'radius', 'latitude', 'longitude', 'passport_number','year_experience','reference', 'fundi_is_middlemen', 'fundi_have_tools', 'fundi_have_smartphone', 'display_seeker_reviews', 'additional_work', 'is_rfq', 'is_package', 'is_hourly', 'price','security_check', 'start_time', 'end_time', 'residential_address','zip_code','address_line_1','is_academy_trained','personal_admin_remarks','personal_admin_rating','technical_admin_remarks','technical_admin_rating','is_personal_verified','is_technical_verified','reference_name1','reference_mobile_number1','reference_name2','reference_mobile_number2','tentative_hour'];

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
    /**
     * Get the city name that belong to this user.
    */
    public function city()
    {
        return $this->belongsTo(City::class,'city_id');
    }
    
    
}
