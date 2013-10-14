<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array('uses' => 'HomeController@showWelcome'));


Route::get('/{customer_name}', array('uses' => 'EntityController@getEntities'));

//if we don't put this route here it is taken by the /{customer_name}/{name} route below ;-)
Route::get('/{customer_name}/reservation', array('uses' => 'ReservationController@getReservations'));

Route::post('/{customer_name}/reservation', 
	array('before' => 'auth.basic', 'uses' => 'ReservationController@createReservation'));

Route::get('/{customer_name}/amenity', array('uses' => 'EntityController@getAmenities'));

Route::get('/{customer_name}/amenity/{name}', array('uses' => 'EntityController@getAmenityByName'));

Route::get('/{customer_name}/{name}', array('uses' => 'EntityController@getEntityByName'));

Route::put('/{customer_name}/{name}', 
	array('before' => 'auth.basic', 'uses' => 'EntityController@createEntity'));

Route::put('/{customer_name}/amenity/{name}', 
	array('before' => 'auth.basic', 'uses' => 'EntityController@createAmenity'));

Route::delete('/{customer_name}/amenity/{name}', 
	array('before' => 'auth.basic', 'uses' => 'EntityController@deleteAmenity'));