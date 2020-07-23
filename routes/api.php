<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace'=>'API'], function(){
	Route::group([
	  'prefix' => 'auth'
	], function() {
		Route::post('register', 'AuthController@index');
		Route::post('providerRegistration', 'AuthController@providerRegistration');
		Route::post('login', 'AuthController@login');
		Route::post('forgotPassword', 'AuthController@forgotPassword');
		Route::post('resetPassword', 'AuthController@resetPassword');
		Route::post('sendOTP', 'AuthController@sendOTP');
		Route::post('sendProviderOTP', 'AuthController@sendProviderOTP');		


		Route::group([
		  'middleware' => 'auth:api'
		], function() {
		    Route::get('logout', 'AuthController@logout');
		    Route::post('updateProfile', 'AuthController@updateProfile');
		    Route::post('mobileVerify', 'AuthController@mobileVerify');
		    Route::post('emailVerify', 'AuthController@emailVerify');
		    
		});
	});
    Route::get('getCountries', 'WebserviceController@getCountries');
    Route::get('getCategories', 'WebserviceController@getCategories');
    Route::post('getSubCategories', 'WebserviceController@getSubCategories');
	


	// APIs that can access after login
	Route::group([
	  'middleware' => 'auth:api'
	], function() {		
		 
		 Route::get('getAdvertisements', 'WebserviceController@getAdvertisements');		 
		 Route::post('getPackages', 'WebserviceController@getPackages');
		 Route::post('addCustomRequirement', 'WebserviceController@addCustomRequirement');
		 Route::post('bookingRequest', 'WebserviceController@bookingRequest');
		 Route::post('getProvidersByLatLong', 'WebserviceController@getProvidersByLatLong');
		 Route::post('getProviderDetail', 'WebserviceController@getProviderDetail');
		 Route::post('addProviderInfo', 'WebserviceController@addProviderInfo');
		 Route::post('addProviderMoreInfo', 'WebserviceController@addProviderMoreInfo');
		 Route::post('getUserProfile', 'WebserviceController@getUserProfile');
		 Route::post('getProvidersJob', 'WebserviceController@getProvidersJob');
	});

	// APIs that can access without login
	// Route::get('public', 'ControllerName@functionName');
	// Route::post('public', 'ControllerName@functionName');
	// Write your routs here...
});
