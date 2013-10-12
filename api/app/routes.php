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
	//API documentation
	return 'Hello World';
});



Route::get('/{customer_name}', function($customer_name = null)
{
	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		$db_entities = DB::table('entity')->where('customer_id', '=', $customer->id);
		$entities = array();

		foreach($db_entities as $entity){
			array_push($entities, json_decode(gunzip($entity->body)));
		}
		return json_encode($entities);
	}
});

//if we don't put this route here it is taken by the /{customer_name}/{name} route below ;-)
Route::get('/{customer_name}/reservation', function($customer_name = null)
{
	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		$reservations = DB::table('reservation')->where('customer_id', '=', $customer->id)->where('from', '>', mktime(0,0,0));
		return json_encode($reservations);
	}
});


Route::post('/{customer_name}/reservation', function($customer_name = null)
{
	//TODO : parse json and JSON schema validation
	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		App::abort(400, 'Entity is already reserved at that time');
		return 'Returns 400 if room is occupied or not open when POST.';

	}
});


Route::get('/{customer_name}/amenity', function($customer_name = null){
	
	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		$amenities = DB::table('entity')
		->where('customer_id', '=', $customer->id)
		->where('type', '=', 'amenity')
		->get();
		return json_encode($amenities);
	}
});

Route::get('/{customer_name}/amenity/{name}', function($customer_name = null, $name = null){

	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		$amenities = DB::table('entity')
		->where('customer_id', '=', $customer->id)
		->where('type', '=', 'amenity')
		->where('name', '=', $name)
		->get();
		return json_encode($amenities);
	}
});

Route::get('/{customer_name}/{name}', function($customer_name = null, $name = null)
{
	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		$reservations = DB::table('reservation')
		->where('customer_id', '=', $customer->id)
		->where('name', '=', $name)
		->get();
		return json_encode($reservations);
	}
});

Route::put('/{customer_name}/{name}', function($customer_name = null, $name = null)
{
	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		//TODO : parse input & JSON schema validation
		DB::table('entity')->insert(
			array(
				'name' => $name,
				'type' => null,
				'updated_at' => 0,
				'created_at' => 0,
				'body' => '',
				'customer_id' => $customer->id
			)
		);
	}
	return 'Adds a new meeting room. JSON accepted. Use json-schema for validation (good solution?)';
});


Route::put('/{customer_name}/amenity/{name}', function($customer_name = null, $name = null){
	
	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		//TODO : parse input & JSON schema validation
		DB::table('entity')->insert(
			array(
				'name' => $name,
				'type' => 'amenity',
				'updated_at' => 0,
				'created_at' => 0,
				'body' => '',
				'customer_id' => $customer->id
			)
		);
	}
	return 'Adds a new kind of amenity when authenticated as customer.';
});

Route::delete('/{customer_name}/amenity/{name}', function($customer_name = null, $name = null){
	
	$customer = DB::table('customer')->where('name', '=', $customer_name)->get();
	if($customer == null){
		App::abort(404, 'Cutomer not found');
	}else{
		DB::table('entity')->where('customer_id', '=', $customer->id)->where('name', '=', $name)->delete();
	}
});