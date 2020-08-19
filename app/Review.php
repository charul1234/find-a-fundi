<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
	public $timestamps = false;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'user_id', 'rating', 'text', 'added_by'
    ]; 
}
