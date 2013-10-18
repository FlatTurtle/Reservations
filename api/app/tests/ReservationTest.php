<?php

class ReservationTest extends TestCase {

	public function setUp()
	{
		parent::setUp();
		 
		Route::enableFilters();
		

		Artisan::call('migrate');
		Artisan::call('db:seed');
		 
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
		
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('GET', '/wrong/amenity/wrong', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
	
	}

	public function testGetNonExistentAmenity() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('GET', '/test/amenity/unexistent', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
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
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('GET', '/wrong/entity/wrong', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
	}

	public function testGetNonExistentEntity() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('GET', '/test/entity/unexistent', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
	}


	public function testCreateEntity() {

		
		$payload = array();
		$payload['name'] = 'Deep Blue';
		$payload['type'] = 'room';
		$payload['body'] = array();
		$payload['body']['name'] = 'Deep Blue';
		$payload['body']['type'] = 'room';
		$payload['body']['opening_hours'] = array();

		for($i=1; $i < 5; $i++){
			$opening_hours = array();
			$opening_hours['opens'] = array('09:00', '13:00');
			$opening_hours['closes'] = array('12:00', '17:00');
			$opening_hours['dayOfWeek'] = $i;
			$opening_hours['validFrom'] = mktime(0,0,0);
			$opening_hours['validThrough'] = mktime(0,0,0) + (365*24*60*60);
			array_push($payload['body']['opening_hours'], $opening_hours);
		}

		$payload['body']['price'] = array();
		$payload['body']['price']['currency'] = 'euro';
		$payload['body']['price']['grouping'] = 'hourly';
		$payload['body']['price']['amount'] = 5;
		$payload['body']['description'] = 'description';
		$payload['body']['location'] = array();
		$payload['body']['location']['map'] = array();
		$payload['body']['location']['map']['img'] = 'http://foo.bar/map.png';
		$payload['body']['location']['map']['reference'] = 'DB';
		$payload['body']['location']['floor'] = 1;
		$payload['body']['location']['building_name'] = 'main';
		$payload['body']['contact'] = 'http://foo.bar/contact.vcf';
		$payload['body']['support'] = 'http://foo.bar/support.vcf';
		$payload['body']['amenities'] = array();

		$crawler = $this->call('PUT', '/test/new_entity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test',
			'CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With' => 'XMLHttpRequest'),
			json_encode($payload));
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

		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\HttpException');

		$payload = array();
		$payload['name'] = '';
		$payload['type'] = 'room';
		$payload['body'] = array();
		$payload['body']['name'] = 'Deep Blue';
		$payload['body']['type'] = 'room';
		$payload['body']['opening_hours'] = array();

		for($i=0; $i < 5; $i++){
			$opening_hours = array();
			$opening_hours['opens'] = array('09:00', '13:00');
			$opening_hours['closes'] = array('12:00', '17:00');
			$opening_hours['dayOfWeek'] = $i;
			$opening_hours['validFrom'] = mktime(0,0,0);
			$opening_hours['validThrough'] = mktime(0,0,0) + (365*24*60*60);
			array_push($payload['body']['opening_hours'], $opening_hours);
		}

		$payload['body']['price'] = array();
		$payload['body']['price']['currency'] = 'euro';
		$payload['body']['price']['grouping'] = 'hourly';
		$payload['body']['price']['amount'] = 5;
		$payload['body']['description'] = '';
		$payload['body']['location'] = array();
		$payload['body']['location']['map'] = array();
		$payload['body']['location']['map']['img'] = 'http://foo.bar/map.png';
		$payload['body']['location']['map']['reference'] = 'DB';
		$payload['body']['location']['floor'] = 1;
		$payload['body']['location']['building_name'] = 'main';
		$payload['body']['contact'] = 'http://foo.bar/contact.vcf';
		$payload['body']['support'] = 'http://foo.bar/support.vcf';
		$payload['body']['amenities'] = array();
		$crawler = $this->call('PUT', '/test/new_entity', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}


	/*public function testCreateReservation() {
		$payload = array();
		$crawler = $this->call('POST', '/test/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
	}*/

	public function testCreateReservationWrongCustomer() {
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$crawler = $this->call('POST', '/wrong/reservation', array(), array(), array('PHP_AUTH_USER' => 'test', 'PHP_AUTH_PW' => 'test'));
		$this->assertTrue($this->client->getResponse()->isOk());
		$this->assertEquals($this->client->getResponse()->getStatusCode(), 404);
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