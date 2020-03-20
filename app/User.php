<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_picture', 'is_active', 'mobile_number', 'address', 'latitude', 'longitude'
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

        // get user image
        if($user->profile_picture!="" && file_exists(public_path(config('constants.USERS_UPLOADS_PATH').$user->profile_picture))){
            $user->profile_picture = url(config('constants.USERS_UPLOADS_PATH').$user->profile_picture);
        }else{
            $user->profile_picture = url(config('constants.NO_IMAGE_URL'));
        }

        // remove extra fields
        unset($user->email_verified_at);
        unset($user->verification_token);
        unset($user->is_active);
        unset($user->deleted_at);
        
        return $user;
    }
}
