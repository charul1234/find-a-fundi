<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackageUser extends Model
{
    protected $table='package_user';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'user_id','package_id','price','is_active'
    ];

    /**
     * Get the package information that belong to this package.
    */
    public function package()
    {
        return $this->belongsTo(Package::class,'package_id');
    }
}
