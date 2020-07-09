<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackageUser extends Model
{
    protected $table='package_user';

    /**
     * Get the package information that belong to this package.
    */
    public function package()
    {
        return $this->belongsTo(Package::class,'package_id');
    }
}
