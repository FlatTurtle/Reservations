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

Route::get('/', function()
{
	return 'Hello World';
});



Route::get('/{customer_name}', function($customer_name = null)
{
	return 'Returns a list of links to things that can be reserved. Customer name is '. $customer_name;
});

//if we don't put this route here it is taken by the /{customer_name}/{name} route below ;-)
Route::get('/{customer_name}/reservation', function($customer_name = null)
{
	return 'returns list of reservations made for the current day';
});

Route::post('/{customer_name}/reservation', function($customer_name = null)
{
	return 'Returns 400 if room is occupied or not open when POST.';
});

Route::get('/{customer_name}/amenity', function($customer_name = null){
	return 'Returns list of amenities.';
});

Route::get('/{customer_name}/amenity/{name}', function($customer_name = null, $name = null){
	return 'Returns information about a certain amenity';
});

Route::get('/{customer_name}/{name}', function($customer_name = null, $name = null)
{
	return 'Returns more information about a certain thing that can be reserved';
});

Route::put('/{customer_name}/{name}', function($customer_name = null, $name = null)
{
	return 'Adds a new meeting room. JSON accepted. Use json-schema for validation (good solution?)';
});


Route::put('/{customer_name}/amenity/{name}', function($customer_name = null, $name = null){
	return 'Adds a new kind of amenity when authenticated as customer.';
});

Route::delete('/{customer_name}/amenity/{name}', function($customer_name = null, $name = null){
	return 'Can remove when auth as customer.';
});