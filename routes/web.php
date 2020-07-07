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
				'users' => 'UsersController',
			]);
			Route::post('users/getUsers', 'UsersController@getUsers')->name('users.getUsers');
			Route::get('users/status/{user_id}', 'UsersController@status')->name('users.status');
			
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
		});
	});
});