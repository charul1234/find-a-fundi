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
        'category_id', 'user_id', 'title','description','location','latitude','longitude','budget','is_rfq','request_for_quote_budget','is_hourly','estimated_hours','min_budget','max_budget','is_package','quantity','datetime','service_datetime','requirement','status'

    ];
}
