<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['verify' => false,'register' => false]);
Route::get('isEmailVerify/{id?}', 'HomeController@isEmailVerify')->name('home.isEmailVerify');
Route::get('/{url?}', 'HomeController@index')->where(['url' => '|home'])->name('home');

Route::group(['middleware' => ['auth']], function(){
	Route::get('access-denied', function(){
		return view('access-denied');
	})->name('access-denied');

	Route::group(['middleware' => ['verified']], function(){
		Route::resource('profile', 'ProfileController')->only(['index', 'store']);

		Route::group(['middleware' => ['check_permission'],'namespace'=>'Admin','prefix'=>'admin', 'as' => 'admin.'], function(){	

			Route::get('dashboard', 'DashboardController@index')->name('dashboard');

			Route::resource('profile', 'ProfileController')->only(['index', 'store']);

			Route::resource('settings', 'SettingsController')->only(['index', 'store']);
			
			Route::resources([
				'providers' => 'ProvidersController',
			]);
			Route::post('providers/getUsers', 'ProvidersController@getUsers')->name('providers.getUsers');
			Route::get('providers/status/{user_id}', 'ProvidersController@status')->name('providers.status');
			Route::get('providers/view/{user_id}', 'ProvidersController@view')->name('providers.view');	
			Route::post('providers/getUsersPackage', 'ProvidersController@getUsersPackage')->name('providers.getUsersPackage');		
			Route::get('providers/company_status/{status}/{id}/{user_id}', 'ProvidersController@company_status')->name('providers.company_status');
			
			Route::resources([
				'categories' => 'CategoriesController',
			]);
			Route::post('categories/getCategories', 'CategoriesController@getCategories')->name('categories.getCategories');
			Route::get('categories/status/{category_id}', 'CategoriesController@status')->name('categories.status');

			Route::resources([
				'packages' => 'PackagesController',
			]);
			Route::post('packages/getPackages', 'PackagesController@getPackages')->name('packages.getPackages');
			Route::get('packages/status/{package_id}', 'PackagesController@status')->name('packages.status');
			/*countries*/
			Route::resources([
				'countries' => 'CountriesController',
			]);
			Route::post('countries/getCountries', 'CountriesController@getCountries')->name('countries.getCountries');
			Route::get('countries/status/{country_id}', 'CountriesController@status')->name('countries.status');
			Route::get('countries/updateDefault/{id}', 'CountriesController@updateDefault')->name('countries.updateDefault');	

			/*cities*/
			Route::resources([
				'cities' => 'CitiesController',
			]);
			Route::post('cities/getCities', 'CitiesController@getCities')->name('cities.getCities');
			Route::get('cities/status/{country_id}', 'CitiesController@status')->name('cities.status');

			/*advertisements*/
			Route::resources([
				'advertisements' => 'AdvertisementsController',
			]);
			Route::post('advertisements/getAdvertisements', 'AdvertisementsController@getAdvertisements')->name('advertisements.getAdvertisements');
			Route::get('advertisements/status/{advertisement_id}', 'AdvertisementsController@status')->name('advertisements.status');
            /*seekers*/
            Route::resources([
				'seekers' => 'SeekersController',
			]);
			Route::post('seekers/getUsers', 'SeekersController@getUsers')->name('seekers.getUsers');
			Route::get('seekers/status/{user_id}', 'SeekersController@status')->name('seekers.status');
			Route::get('seekers/view/{user_id}', 'SeekersController@view')->name('seekers.view');	
		});
	});
});