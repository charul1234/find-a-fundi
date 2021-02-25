<?php

return [
    /*
     * Pesapal consumer key
     */
    'consumer_key'    => 'pcNFCAe5iQC60PoT4qvgXHcwx4pwRFJ6',

    /*
     * Pesapal consumer secret
     */
    'consumer_secret' => 'sWALyrArPrAqxuKX',

    /*
     * ISO code for the currency
     */
    'currency'        => 'USD',

    /*
     * controller method to call for instant notifications IPN as relative path from App\Http\Controllers\
     * eg "TransactionController@confirmation"
     */
    'ipn'             => env('PESAPAL_IPN'),

    /*
     * Pesapal environment
     */
    'live'            => env('PESAPAL_LIVE', false),

    /*
     * Route name to handle the callback
     * eg Route::get('donepayment', ['as' => 'paymentsuccess', 'uses'=>'PaymentsController@paymentsuccess']);
     * The route name is "paymentsuccess"
     */
    'callback_route'  => env('PESAPAL_CALLBACK_ROUTE'),

];