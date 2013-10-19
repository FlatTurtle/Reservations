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
		$this->amenity_payload['name'] = 'wifi';
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
		 
	}

	//TODO : build json values to test test test !!!

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testGetEntities()
	{
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/test', $headers);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));
	}

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testGetReservations()
	{
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/test/reservation', $headers);
		$this->assertEquals($request->status_code, 200);
		$this->assertNotNull(json_decode($request->body));
	}

	public function testGetReservationWrongCustomer() {
		
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/wrong/reservation', $headers);
		$this->assertEquals($request->status_code, 404);
	}



	public function testGetAmenities() {
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/test/amenity', $headers);
		$this->assertEquals($request->status_code, 200);
	}

	public function testGetAmenitiesWrongCustomer() {
		$headers = array('Accept' => 'application/json');
		$request = Requests::get(Config::get('app.url'). '/wrong/amenity', $headers);
		$this->assertEquals($request->status_code, 404);
	}


	public function testCreateAmenity() {
		
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/amenity/test_amenity', $headers, 
			$this->amenity_payload, $options);
		$this->assertEquals($request->status_code, 200);

	}

	/*public function testCreateAmenityWrongCustomer() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('PUT', '/test/amenity/test_amenity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
	}

	public function testCreateMalformedAmenity() {
		$payload = array();
		$crawler = $this->call('PUT', '/test/amenity/test_amenity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	

	}

	public function testCreateAmenityMissingParameters() {
		$payload = array();
		$crawler = $this->call('PUT', '/test/amenity/test_amenity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/



	/*public function testGetAmenity() {

	}*/

	public function testGetAmenityWrongCustomer() {
		
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::get(Config::get('app.url'). '/wrong/amenity/wrong', $headers, $options);
		$this->assertEquals($request->status_code, 404);
	
	}

	public function testGetNonExistentAmenity() {
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::get(Config::get('app.url'). '/test/amenity/unknown', $headers, $options);
		$this->assertEquals($request->status_code, 404);
	}


	/*public function testDeleteAmenity() {
		$payload = array();
		$crawler = $this->call('DELETE', '/test/amenity/test_amenity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/

	/*public function testDeleteAmenityWrongCustomer() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('DELETE', '/test/amenity/test_amenity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
	}

	public function testDeleteNonExistentAmenity() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('DELETE', '/test/amenity/nonexistent', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
	}*/


	/*public function testGetEntity() {

	}*/

	public function testGetEntityWrongCustomer() {
		
		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::get(Config::get('app.url'). '/wrong/entity/wrong', $headers, $options);
		$this->assertEquals($request->status_code, 404);
	}

	public function testGetNonExistentEntity() {

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::get(Config::get('app.url'). '/test/entity/unknown', $headers, $options);
		$this->assertEquals($request->status_code, 404);

	}


	public function testCreateEntity() {

		$crawler = $this->call('PUT', '/test/new_entity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test',
			'CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With' => 'XMLHttpRequest'),
			json_encode($this->payload));
	}


	/*public function testCreateEntityWrongCustomer() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('PUT', '/test/new_entity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
	}*/

	/*public function testCreateMalformedEntity() {
		$payload = array();
		$crawler = $this->call('PUT', '/test/new_entity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/

	public function testCreateEntityMissingParameters() {

	
		$payload = $this->payload;
		$payload['name'] = '';

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['name'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

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
		$payload['body']['name'] = null;
		$request = Requests::put(Config::get('app.url'). '/test/new_entity', $headers, $payload, $options);
		$this->assertEquals($request->status_code, 400);

		$payload = $this->payload;
		$payload['body']['name'] = '';
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


	/*public function testCreateReservation() {
		$payload = array();
		$crawler = $this->call('POST', '/test/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/

	public function testCreateReservationWrongCustomer() {

		$headers = array('Accept' => 'application/json');
		$options = array('auth' => array('test', 'test'));
		$request = Requests::get(Config::get('app.url'). '/wrong/reservation', $headers, $options);
		$this->assertEquals($request->status_code, 404);
	}

	/*public function testCreateReservationMissingParameters() {
		$payload = array();
		$crawler = $this->call('POST', '/test/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/

	/*public function testCreateMalformedReservation() {
		$payload = array();
		$crawler = $this->call('POST', '/test/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/

	/*public function testCreateOldReservation() {
		$payload = array();
		$crawler = $this->call('POST', '/test/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/

	/*public function testCreateAlreadyBookedReservation() {
		$payload = array();
		$crawler = $this->call('POST', '/test/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/



	



	

}


?>