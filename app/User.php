<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPassword;
#use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use PushNotification;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasApiTokens, Notifiable,/* SoftDeletes,*/ HasRoles, HasMediaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'mobile_number','facebook_id','facebook_data','google_plus_id','google_plus_data','is_online','email_verified_at','device_type','device_id','device_token','screen_name','is_verify','is_mobile_verify','is_email_verify','ratings'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token){
        $this->notify(new ResetPassword($token));
    }

    // For get user detail
    public function getUserDetail(){
        $user = $this;
        $user->profile_picture = asset($user->getFirstMediaUrl('profile_picture'));
        $user->profiles;
        // remove extra fields
        unset($user->email_verified_at);
        unset($user->verification_token);
        unset($user->is_active);
        unset($user->deleted_at);
        
        return $user;
    }

    /**
     * Get the profiles for the blog post.
     */
    public function profiles()
    {
        return $this->hasOne(Profile::class);
    }

    public function registerMediaCollections(){
        $this->addMediaCollection('profile_picture')
        ->useFallbackUrl(asset(config('constants.NO_IMAGE_URL')))
        ->useFallbackPath(public_path(config('constants.NO_IMAGE_URL')))
        ->singleFile();     
         $this->addMediaCollection('certificate_conduct')
        ->useFallbackUrl(asset(config('constants.NO_IMAGE_URL')))
        ->useFallbackPath(public_path(config('constants.NO_IMAGE_URL')))
        ->singleFile(); 
         $this->addMediaCollection('nca')
        ->useFallbackUrl(asset(config('constants.NO_IMAGE_URL')))
        ->useFallbackPath(public_path(config('constants.NO_IMAGE_URL')))
        ->singleFile(); 
        $this->addMediaCollection('passport_image')
        ->useFallbackUrl(asset(config('constants.NO_IMAGE_URL')))
        ->useFallbackPath(public_path(config('constants.NO_IMAGE_URL')))
        ->singleFile(); 
    } 
    /**
     * Get the profile information that belong to this user.
    */
    public function profile()
    {
        return $this->belongsTo(Profile::class,'id','user_id');
    }
    /**
     * Get the package user information that belong to this user.
    */
    public function hourly_charge()
    {
        return $this->hasMany(HourlyCharge::class,'user_id');
    }
    /**
     * Get the package user information that belong to this user.
    */
    public function package_user()
    {
        return $this->hasMany(PackageUser::class,'user_id','id');
    }
    /**
     * Get the company information that belong to this user.
    */
    public function company()
    {
        return $this->hasMany(Company::class,'user_id');
    }

    /**
     * Get the certification information that belong to this user.
    */
    public function certification()
    {
        return $this->hasMany(Certification::class,'user_id');
    }

    /**
     * Get the category information that belong to this user.
    */
    public function category_user()
    {
        return $this->hasMany(CategoryUser::class,'user_id');
    }
    /**
     * Get the review information that belong to this user.
    */

    public function review()
    {
        return $this->hasMany(Review::class,'user_id');
    }

     /**
     * Route notifications for the Apn channel.
     *
     * @return string|array
     */
    public function routeNotificationForApn(){
        return $this->device_token;
    }


    /**
     * Route notifications for the Fcm channel.
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        return $this->device_token;
    }

    /**
     * For get Rating number of provider
     *
     * @return \Illuminate\Http\Response
    */   
    public static function getAdminRating()
    { 
       $sections= array('1'=>'1', 
                        '2'=>'2', 
                        '3'=>'3', 
                        '4'=>'4',
                        '5'=>'5');
        return $sections;        
    }

    

}
