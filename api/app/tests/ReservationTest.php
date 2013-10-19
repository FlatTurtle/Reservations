<?php

class ReservationTest extends TestCase {

	public function setUp()
	{
		parent::setUp();
		 
		Route::enableFilters();
		

		Artisan::call('migrate');
		Artisan::call('db:seed');

		$this->payload = array();
		$this->payload['name'] = 'Deep Blue';
		$this->payload['type'] = 'room';
		$this->payload['body'] = array();
		$this->payload['body']['name'] = 'Deep Blue';
		$this->payload['body']['type'] = 'room';
		$this->payload['body']['opening_hours'] = array();

		for($i=1; $i < 5; $i++){
			$opening_hours = array();
			$opening_hours['opens'] = array('09:00', '13:00');
			$opening_hours['closes'] = array('12:00', '17:00');
			$opening_hours['dayOfWeek'] = $i;
			$opening_hours['validFrom'] = date("Y-m-d h:m:s", time()+60*60*24);
			$opening_hours['validThrough'] =  date("Y-m-d h:m:s", time()+(365*24*60*60));
			array_push($this->payload['body']['opening_hours'], $opening_hours);
		}

		$this->payload['body']['price'] = array();
		$this->payload['body']['price']['currency'] = 'EUR';
		$this->payload['body']['price']['grouping'] = 'hourly';
		$this->payload['body']['price']['amount'] = 5;
		$this->payload['body']['description'] = 'description';
		$this->payload['body']['location'] = array();
		$this->payload['body']['location']['map'] = array();
		$this->payload['body']['location']['map']['img'] = 'http://foo.bar/map.png';
		$this->payload['body']['location']['map']['reference'] = 'DB';
		$this->payload['body']['location']['floor'] = 1;
		$this->payload['body']['location']['building_name'] = 'main';
		$this->payload['body']['contact'] = 'http://foo.bar/contact.vcf';
		$this->payload['body']['support'] = 'http://foo.bar/support.vcf';
		$this->payload['body']['amenities'] = array();


		$this->amenity_payload = array();
		$this->amenity_payload['description'] = 'Broadband wireless connection in every meeting room.';
		$this->amenity_payload['schema'] = array();
		$this->amenity_payload['schema']['$schema'] = "http://json-schema.org/draft-04/schema#";
		$this->amenity_payload['schema']['title'] = 'wifi';
		$this->amenity_payload['schema']['description'] = 'Broadband wireless connection in every meeting room.';
		$this->amenity_payload['schema']['type'] = 'object';
		$this->amenity_payload['schema']['properties'] = array();
		$this->amenity_payload['schema']['properties']['essid'] = array();
		$this->amenity_payload['schema']['properties']['essid']['description'] = 'Service set identifier.';
		$this->amenity_payload['schema']['properties']['essid']['type'] = 'string';
		$this->amenity_payload['schema']['properties']['label'] = array();
		$this->amenity_payload['schema']['properties']['label']['description'] = 'Simple label.';
		$this->amenity_payload['schema']['properties']['label']['type'] = 'string';
		$this->amenity_payload['schema']['properties']['code'] = array();
		$this->amenity_payload['schema']['properties']['code']['description'] = 'Authentication code.';
		$this->amenity_payload['schema']['properties']['code']['type'] = 'string';
		$this->amenity_payload['schema']['properties']['encryption'] = array();
		$this->amenity_payload['schema']['properties']['encryption']['description'] = 'Encryption system (e.g. WEP, WPA, WPA2).';
		$this->amenity_payload['schema']['properties']['encryption']['type'] = 'string';
		$this->amenity_payload['schema']['required'] = array('essid', 'code');
		 
		$this->reservation_payload = array();
		$this->reservation_payload['entity'] = null;
		$this->reservation_payload['type'] = null;
		$this->reservation_payload['time'] = array();
		$this->reservation_payload['time']['from'] = date('c', time());
		$this->reservation_payload['time']['to'] = date('c', time() + (60*60*2));
		$this->reservation_payload['subject'] = 'subject';
		$this->reservation_payload['comment'] = 'comment';
		$this->reservation_payload['announce'] = array('yeri', 'pieter', 'nik', 'quentin');

	}

	public function testCreateAmenity() {
		
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/amenity/test_amenity', $headers, 
			$this->amenity_payload, $options);
		$this->assertEquals($request->status_code, 200);

	}

	public function testCreateAmenityInexistentCustomer() {

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/unknown/amenity/test_amenity', $headers, 
			$this->amenity_payload, $options);
		$this->assertEquals($request->status_code, 404);
	}

	public function testCreateAmenityWrongCustomer() {

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test2/amenity/test_amenity', $headers, 
			$this->amenity_payload, $options);
		$this->assertEquals($request->status_code, 403);
	}

	public function testCreateAmenityWrongCredentials() {

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'wrong password'));
		$request = Requests::put(Config::get('app.url'). '/test/amenity/test_amenity', $headers, 
			$this->amenity_payload, $options);
		$this->assertEquals($request->status_code, 401);
	}


	public function testCreateMalformedAmenity() {


		$amenity_payload = $this->amenity_payload;
		$amenity_payload['description'] = '';
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/amenity/test_amenity', $headers, 
			$amenity_payload, $options);
		$this->assertEquals($request->status_code, 400);

		$amenity_payload = $this->amenity_payload;
		$amenity_payload['description'] = null;
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/amenity/test_amenity', $headers, 
			$amenity_payload, $options);
		$this->assertEquals($request->status_code, 400);

		$amenity_payload = $this->amenity_payload;
		$amenity_payload['schema'] = null;
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/amenity/test_amenity', $headers, 
			$amenity_payload, $options);
		$this->assertEquals($request->status_code, 400);
	}


	public function testGetAmenities() {
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/test/amenity', $headers);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));
	}

	public function testGetAmenitiesWrongCustomer() {
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/wrong/amenity', $headers);
		$this->assertEquals($request->status_code, 404);
	}

	public function testGetAmenity() {
		
		$amenity_payload = $this->amenity_payload;
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/amenity/get_amenity', $headers, 
			$amenity_payload, $options);
		$this->assertEquals($request->status_code, 200);

		$request = Requests::get(Config::get('app.url'). '/test/amenity/get_amenity', $headers, $options);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));


	}

	public function testGetAmenitiesNonExistentCustomer() {
		
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/unkown/amenity', $headers);
		$this->assertEquals($request->status_code, 404);
	}

	public function testGetNonExistentAmenity() {
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/test/amenity/unknown', $headers);
		$this->assertEquals($request->status_code, 404);
	}


	public function testDeleteAmenity() {

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/amenity/to_delete', $headers, 
			$this->amenity_payload, $options);
		$this->assertEquals($request->status_code, 200);

		$request = Requests::delete(Config::get('app.url'). '/test/amenity/to_delete', $headers, $options);
		$this->assertEquals($request->status_code, 200);

	}

	public function testDeleteAmenityWrongCustomer() {
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::delete(Config::get('app.url'). '/test2/amenity/test_amenity', $headers, $options);
		$this->assertEquals($request->status_code, 403);
	}

	public function testDeleteNonExistentAmenity() {
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::delete(Config::get('app.url'). '/test/amenity/to_delete', $headers, $options);
		$this->assertEquals($request->status_code, 404);
	}


	public function testCreateEntity() {

		$payload = $this->payload;
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/create_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 200);
	}


	public function testCreateEntityWrongCustomer() {
		$payload = $this->payload;
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test2/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 403);
	}

	public function testCreateExistingEntity() {
		$payload = $this->payload;
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/existing_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 200);

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/existing_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);
	}

	public function testCreateEntityMissingParameters() {

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$payload = $this->payload;
		$payload['type'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['type'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['type'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['type'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['contact'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['contact'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['contact'] = 'not a url';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['support'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['support'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['support'] = 'not a url';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['opening_hours'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['amount'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['amount'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['amount'] = -1;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['currency'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['currency'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['currency'] = 'pokethunes';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['grouping'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['grouping'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['price']['grouping'] = 'not a good one';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['opening_hours'] = array();
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['opening_hours'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		//TODO : need to check every field of opening_hours
		$payload = $this->payload;
		$payload['body']['opening_hours'] = array();
		$opening_hour = array();
		$opening_hours['opens'] = array('09:00', '13:00');
		$opening_hours['closes'] = array('12:00', '17:00');
		$opening_hours['dayOfWeek'] = 1;
		$opening_hours['validFrom'] = date("Y-m-d h:m:s", time()+60*60*24);
		$opening_hours['validThrough'] =  date("Y-m-d h:m:s", time()+(365*24*60*60));
		array_push($this->payload['body']['opening_hours'], $opening_hours);
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['map'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['map'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['floor'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['floor'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['floor'] = 'not an int';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['building_name'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['building_name'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['map']['img'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['map']['img'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['map']['img'] = 'not a url';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['map']['reference'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['location']['map']['reference'] = '';
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['amenities'] = array('unknown amenities');
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);
		
	}

	public function testGetEntities()
	{
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/test', $headers);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));
	}

	public function testGetEntity() {
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/get_entity', $headers, $this->payload, $options);
		$this->assertEquals($request->status_code, 200);

		$request = Requests::get(Config::get('app.url'). '/test/get_entity', $headers);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));
	}

	public function testGetEntityWrongCustomer() {
		
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/wrong/entity/wrong', $headers);
		$this->assertEquals($request->status_code, 404);
	}

	public function testGetNonExistentEntity() {

		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/test/entity/unknown', $headers);
		$this->assertEquals($request->status_code, 404);

	}





	public function testCreateReservation() {

		$payload = $this->payload;
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/reservation_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 200);

		$payload = $this->reservation_payload;
		$payload['entity'] = 'reservation_entity';
		$payload['type'] = 'room';
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::get(Config::get('app.url'). '/test2/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));
	}

	public function testCreateReservationWrongCustomer() {

		$payload = $this->reservation_payload;
		$payload['time']['from'] = time();
		$payload['time']['to'] = time() + (60*60*2);
		$payload['announce'] = array('yeri', 'pieter', 'nik', 'quentin');
		$payload['entity'] = 'reservation_entity';
		$payload['type'] = 'room';
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::post(Config::get('app.url'). '/test2/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 403);
	}

	public function testCreateReservationMissingParameters() {

		$this->reservation_payload['entity'] = 'reservation_entity';
		$this->reservation_payload['type'] = 'room';
		
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));

		$payload = $this->reservation_payload;
		$payload['entity'] = '';
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['entity'] = null;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['type'] = '';
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['type'] = null;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['time'] = null;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['time']['from'] = null;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['time']['from'] = -1;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['time']['from'] = time()-1;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['time']['to'] = null;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['time']['to'] = -1;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['time']['to'] = time()-1;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['time']['to'] = time();
		$payload['time']['from'] = $payload['time']['to'] - 1;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['comment'] = '';
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['comment'] = null;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['subject'] = '';
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['subject'] = null;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['announce'] = null;
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->reservation_payload;
		$payload['announce'] = '';
		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);





	}

	
	//TODO : work on times to check validation
	/*public function testCreateAlreadyBookedReservation() {
		$payload = $this->payload;
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));

		$request = Requests::put(Config::get('app.url'). '/test/already_booked_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 200);

		$payload = $this->reservation_payload;
		$payload['entity'] = 'already_booked_entity';
		$payload['type'] = 'room';
		$payload['time']['from'] = time()+(60*60);
		$payload['time']['to'] = $payload['time']['from'] + (60*60*2);

		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 200);

		$request = Requests::post(Config::get('app.url'). '/test/reservation', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);



	}

	public function testCreateReservationOnUnavailableEntity() {

	}*/

	public function testGetReservations()
	{
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/test/reservation', $headers);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));

		$data = array('day' => date('Y-m-d', time()));	
		$request = Requests::get(Config::get('app.url'). '/test/reservation', $headers, $data);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));


	}

	public function testGetReservationWrongCustomer() {
		
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/wrong/reservation', $headers);
		$this->assertEquals($request->status_code, 404);
	}

}


?>