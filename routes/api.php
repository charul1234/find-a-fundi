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
		    Route::post('updateProviderProfile', 'AuthController@updateProviderProfile');
		    
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
		 //Route::post('getProvidersJob', 'WebserviceController@getProvidersJob');
		 Route::post('getJob', 'WebserviceController@getJob');
		 Route::post('getMyJobs', 'WebserviceController@getMyJobs');
		 Route::post('getSeekerProfile', 'WebserviceController@getSeekerProfile');
		 Route::post('getJobDetail', 'WebserviceController@getJobDetail');
		 Route::post('jobDeclined', 'WebserviceController@jobDeclined');
		 Route::post('jobQuote', 'WebserviceController@jobQuote');
		 Route::post('getRFQProviders', 'WebserviceController@getRFQProviders');
		 Route::post('getBookingDetail', 'WebserviceController@getBookingDetail');
		 Route::post('makePayment', 'WebserviceController@makePayment');
		 Route::post('getFAQ', 'WebserviceController@getFAQ');
		 Route::post('addRating', 'WebserviceController@addRating');
		 Route::post('addJobsSchedule', 'WebserviceController@addJobsSchedule');
		 Route::post('getProviderByPackage', 'WebserviceController@getProviderByPackage');
		 Route::post('bookingPackage', 'WebserviceController@bookingPackage');
		 Route::post('getProviderScheduleList', 'WebserviceController@getProviderScheduleList');
		 Route::post('updateProvidersSchedule', 'WebserviceController@updateProvidersSchedule');
		 Route::post('updateJob', 'WebserviceController@updateJob');
	});

	// APIs that can access without login
	// Route::get('public', 'ControllerName@functionName');
	// Route::post('public', 'ControllerName@functionName');
	// Write your routs here...
});
