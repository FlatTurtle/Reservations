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
		 
	}

	//TODO : build json values to test test test !!!

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testGetEntities()
	{
		
		$crawler = $this->call('GET', '/test', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));	
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 200);		
		
	}

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testGetReservations()
	{
		
		$crawler = $this->call('GET', '/test/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 200);
				
	}

	public function testGetReservationWrongCustomer() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('GET', '/wrong/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
	}



	public function testGetAmenities() {
		$crawler = $this->call('GET', '/test/amenity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 200);
	}

	public function testGetAmenitiesWrongCustomer() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('GET', '/wrong/amenity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);

	}


	public function testCreateAmenity() {
		$payload = array();
		$payload['name'] = 'wifi';
		$payload['description'] = 'Broadband wireless connection in every meeting room.';
		$payload['schema'] = array();
		$payload['schema']['$schema'] = "http://json-schema.org/draft-04/schema#";
		$payload['schema']['title'] = 'wifi';
		$payload['schema']['description'] = 'Broadband wireless connection in every meeting room.';
		$payload['schema']['type'] = 'object';
		$payload['schema']['properties'] = array();
		$payload['schema']['properties']['essid'] = array();
		$payload['schema']['properties']['essid']['description'] = 'Service set identifier.';
		$payload['schema']['properties']['essid']['type'] = 'string';
		$payload['schema']['properties']['label'] = array();
		$payload['schema']['properties']['label']['description'] = 'Simple label.';
		$payload['schema']['properties']['label']['type'] = 'string';
		$payload['schema']['properties']['code'] = array();
		$payload['schema']['properties']['code']['description'] = 'Authentication code.';
		$payload['schema']['properties']['code']['type'] = 'string';
		$payload['schema']['properties']['encryption'] = array();
		$payload['schema']['properties']['encryption']['description'] = 'Encryption system (e.g. WEP, WPA, WPA2).';
		$payload['schema']['properties']['encryption']['type'] = 'string';
		$payload['schema']['required'] = array('essid', 'code');

		/*$payload = array();
		$payload['name'] = 'phone';
		$payload['description'] = 'Internal phone system.';
		$payload['schema'] = array();
		$payload['schema']['$schema'] = "http://json-schema.org/draft-04/schema#";
		$payload['schema']['title'] = 'phone';
		$payload['schema']['description'] = 'Internal phone system.';
		$payload['schema']['type'] = 'object';
		$payload['schema']['properties'] = array();
		$payload['schema']['properties']['number'] = array();
		$payload['schema']['properties']['number']['description'] = 'Telephone number';
		$payload['schema']['properties']['number']['type'] = 'string';
		$payload['schema']['properties']['number'] = array();
		$payload['schema']['properties']['number']['extension'] = 'International phone extension';
		$payload['schema']['properties']['number']['type'] = 'string';
		$payload['schema']['properties']['label'] = array();
		$payload['schema']['properties']['label']['description'] = 'Simple label';
		$payload['schema']['properties']['label']['type'] = 'string';
		$payload['schema']['required'] = array('number');*/

		
		$crawler = $this->call('PUT', '/test/amenity/test_amenity', array(), array(), 
			array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test', 'CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With' => 'XMLHttpRequest'),
			json_encode($payload));
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