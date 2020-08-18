<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'user_id', 'booking_id', 'transaction_type', 'trans_id', 'trans_time', 'trans_amount', 'business_shortcode','bill_ref_number','invoice_number','third_party_trans_id','msisdn','first_name','middle_name','last_name','org_account_balance','status','payment_mode','payment_by'
    ]; 
}
