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

App::error(function(\Symfony\Component\HttpKernel\Exception\HttpException $e)
{
    return Response::json(array(
        'error' => $e->getMessage(),
    ), $e-> getStatusCode());
 });

/*
	Root url, this is where the API documentation will be display.
*/
Route::get('/', array('uses' => 'HomeController@showWelcome'));

/*
	GET http://reservation.hostname/{customer_name} : return a list of things that can be reserved
*/
Route::get('/{customer_name}', array('uses' => 'EntityController@getEntities'));


/*
	GET http://reservation.hostname/{customer_name}/amenity : returns list of amenities/
*/
Route::get('/{customer_name}/amenity', array('uses' => 'EntityController@getAmenities'));

/*
	GET http://reservation.hostname/{customer_name}/amenity/{name} : returns information about a
	certain amenity.
*/
Route::get('/{customer_name}/amenity/{name}', array('uses' => 'EntityController@getAmenityByName'));

/*
	PUT http://reservation.hostname/{customer_name}/amenity/{name} : create or update an amenity.
*/
Route::put('/{customer_name}/amenity/{name}', 
	array('before' => 'auth.basic', 'uses' => 'EntityController@createAmenity'));

/*
	DELETE http://reservation.hostname/{customer_name}/amenity/{name} : delete a certain amenity
*/
Route::delete('/{customer_name}/amenity/{name}', 
	array('before' => 'auth.basic', 'uses' => 'EntityController@deleteAmenity'));

/*
	GET http://reservation.hostname/{customer_name}/reservation : returns list of reservations made
	for the current day. Day can be changed with the GET parameter ?day=2013-10-12
*/
Route::get('/{customer_name}/reservation', array('uses' => 'ReservationController@getReservations'));

/*
	POST http://reservation.hostname/{customer_name}/reservation : create a new reservation.
*/
Route::post('/{customer_name}/reservation', 
	array('before' => 'auth.basic', 'uses' => 'ReservationController@createReservation'));

/*
	GET http://reservation.hostname/{customer_name}/reservation/{id} : return the reservation with
	id {id}.
*/
Route::get('/{customer_name}/reservation/{id}', 
	array('uses' => 'ReservationController@getReservation'));
/*
	POST http://reservation.hostname/{customer_name}/reservation/{id} : update the reservation with
	id {id}.
*/
Route::post('/{customer_name}/reservation/{id}', 
	array('before' => 'auth.basic', 'uses' => 'ReservationController@updateReservation'));

/* 
	DELETE http://reservation.hostname/{customer_name}/reservation/{id} : cancel the reservation {id}
*/
Route::delete('/{customer_name}/reservation/{id}', 
	array('before' => 'auth.basic', 'uses' => 'ReservationController@deleteReservation'));

/*
	GET http://reservation.hostname/{customer_name}/{name} : returns informations about a certain
	thing that can be reserved.
*/
Route::get('/{customer_name}/{name}', array('uses' => 'EntityController@getEntityByName'));

/*
	PUT http://reservation.hostname/{customer_name}/{name} : create or update thing that can be reserved.
*/
Route::put('/{customer_name}/{name}', 
	array('before' => 'auth.basic', 'uses' => 'EntityController@createEntity'));

