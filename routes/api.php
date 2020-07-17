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

		Route::group([
		  'middleware' => 'auth:api'
		], function() {
		    Route::get('logout', 'AuthController@logout');
		    Route::post('updateProfile', 'AuthController@updateProfile');
		});
	});
    Route::get('getCountries', 'WebserviceController@getCountries');
    Route::get('getCategories', 'WebserviceController@getCategories');
    Route::post('getSubCategoriesByCategoryId', 'WebserviceController@getSubCategoriesByCategoryId');
	


	// APIs that can access after login
	Route::group([
	  'middleware' => 'auth:api'
	], function() {		
		 
		 Route::get('getAdvertisements', 'WebserviceController@getAdvertisements');		 
		 Route::post('getPackagesBySubCategoryId', 'WebserviceController@getPackagesBySubCategoryId');
		 Route::post('addCustomRequirement', 'WebserviceController@addCustomRequirement');
		 Route::post('addSendRequest', 'WebserviceController@addSendRequest');
		 Route::post('getProvidersByLatLong', 'WebserviceController@getProvidersByLatLong');
		 Route::post('getProviderDetail', 'WebserviceController@getProviderDetail');
		 Route::post('addProviderInfo', 'WebserviceController@addProviderInfo');
	});

	// APIs that can access without login
	// Route::get('public', 'ControllerName@functionName');
	// Route::post('public', 'ControllerName@functionName');
	// Write your routs here...
});
