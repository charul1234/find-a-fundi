<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'country_id', 'city_id', 'experience_level_id', 'payment_option_id', 'dob', 'facebook_url', 'twitter_url', 'linkedin_url', 'googleplus_url', 'instagram_url', 'work_address', 'radius', 'latitude', 'longitude', 'passport_number', 'fundi_is_middlemen', 'fundi_have_tools', 'fundi_have_smartphone', 'display_seeker_reviews'
    ];

    // For get user
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
