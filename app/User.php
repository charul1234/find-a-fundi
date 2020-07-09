<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasApiTokens, Notifiable, SoftDeletes, HasRoles, HasMediaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'mobile_number'
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
        return $this->hasMany(PackageUser::class,'user_id');
    }
    /**
     * Get the company information that belong to this user.
    */
    public function company()
    {
        return $this->hasMany(Company::class,'user_id');
    }

}
