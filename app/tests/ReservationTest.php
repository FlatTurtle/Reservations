<?php
/**
 *
 *
 */

/**
 *
 *
 */
class ReservationTest extends TestCase
{
    public static $headers = array(
        'HTTP_Accept' => 'application/json', 
    );

    /**
     *
     *
     */
    public function setUp()
    {
        parent::setUp();
         
        Route::enableFilters();

        Artisan::call('migrate');
        Artisan::call('db:seed');

        $this->test_cluster = DB::table('cluster')->where('clustername', '=', 'test')->first();
        $this->admin_cluster = DB::table('cluster')->where('clustername', '=', 'admin')->first();
                
        $this->entity_payload = array();
        $this->entity_payload['name'] = 'Deep Blue';
        $this->entity_payload['type'] = 'room';
        $this->entity_payload['body'] = array();
        $this->entity_payload['body']['name'] = 'Deep Blue';
        $this->entity_payload['body']['type'] = 'room';
        $this->entity_payload['body']['opening_hours'] = array();

        for ($i=1; $i < 5; $i++) {
                $opening_hours = array();
                $opening_hours['opens'] = array('09:00', '13:00');
                $opening_hours['closes'] = array('12:00', '17:00');
                $opening_hours['dayOfWeek'] = $i;
                $opening_hours['validFrom'] = date(
                    "Y-m-d h:m:s", time()+60*60*24
                );
                $opening_hours['validThrough'] = date(
                    "Y-m-d h:m:s", time()+(365*24*60*60)
                );
                array_push(
                    $this->entity_payload['body']['opening_hours'], 
                    $opening_hours
                );
        }

        $this->entity_payload['body']['price'] = array();
        $this->entity_payload['body']['price']['currency'] = 'EUR';
        $this->entity_payload['body']['price']['hourly'] = 5;
        $this->entity_payload['body']['price']['daily'] = 40;
        $this->entity_payload['body']['description'] = 'description';
        $this->entity_payload['body']['location'] = array();
        $this->entity_payload['body']['location']['map'] = array();
        $this->entity_payload['body']['location']['map']['img'] 
            = 'http://foo.bar/map.png';
        $this->entity_payload['body']['location']['map']['reference'] = 'DB';
        $this->entity_payload['body']['location']['floor'] = 1;
        $this->entity_payload['body']['location']['building_name'] = 'main';
        $this->entity_payload['body']['contact'] = 'http://foo.bar/contact.vcf';
        $this->entity_payload['body']['support'] = 'http://foo.bar/support.vcf';
        $this->entity_payload['body']['amenities'] = array();


        $this->amenity_payload = array();
        $this->amenity_payload['description'] 
            = 'Broadband wireless connection in every meeting room.';
        $this->amenity_payload['schema'] 
            = array();
        $this->amenity_payload['schema']['$schema'] 
            = "http://json-schema.org/draft-04/schema#";
        $this->amenity_payload['schema']['title'] 
            = 'wifi';
        $this->amenity_payload['schema']['description'] 
            = 'Broadband wireless connection in every meeting room.';
        $this->amenity_payload['schema']['type'] 
            = 'object';
        $this->amenity_payload['schema']['properties'] 
            = array();
        $this->amenity_payload['schema']['properties']['essid'] 
            = array();
        $this->amenity_payload['schema']['properties']['essid']['description'] 
            = 'Service set identifier.';
        $this->amenity_payload['schema']['properties']['essid']['type'] 
            = 'string';
        $this->amenity_payload['schema']['properties']['label'] 
            = array();
        $this->amenity_payload['schema']['properties']['label']['description'] 
            = 'Simple label.';
        $this->amenity_payload['schema']['properties']['label']['type'] 
            = 'string';
        $this->amenity_payload['schema']['properties']['code'] 
            = array();
        $this->amenity_payload['schema']['properties']['code']['description'] 
            = 'Authentication code.';
        $this->amenity_payload['schema']['properties']['code']['type'] 
            = 'string';
        $this->amenity_payload['schema']['properties']['encryption'] 
            = array();
        $this->amenity_payload['schema']['properties']['encryption']['description'] 
            = 'Encryption system (e.g. WEP, WPA, WPA2).';
        $this->amenity_payload['schema']['properties']['encryption']['type'] 
            = 'string';
        $this->amenity_payload['schema']['required'] 
            = array('essid', 'code');
         
        $this->reservation_payload 
            = array();
        $this->reservation_payload['thing'] 
            = null;
        $this->reservation_payload['type'] 
            = null;
        $this->reservation_payload['time'] 
            = array();
        $this->reservation_payload['time']['from'] 
            = date('c', time());
        $this->reservation_payload['time']['to'] 
            = date('c', time() + (60*60*2));
        $this->reservation_payload['subject'] 
            = 'subject';
        $this->reservation_payload['comment'] 
            = 'comment';
        $this->reservation_payload['announce'] 
            = array('yeri', 'pieter', 'nik', 'quentin');

    }

    /**
     *
     * @group amenity
     * @group create
     * @return null null
     */
    public function testCreateAmenity()
    {
        Auth::loginUsingId($this->test_cluster->id);
        $response = $this->call(
            'PUT',
            'test/amenities/test_amenity',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($this->amenity_payload)
        );
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertNotNull(json_decode($response->getContent()));
        Auth::logout();   
    }

    /**
     *
     * @group amenity
     * @group create
     * @return null
     */
    public function testCreateAmenityAdmin()
    {
        Auth::loginUsingId($this->admin_cluster->id);
        $payload = $this->amenity_payload;
        $response = $this->call(
            'PUT',
            'test/amenities/admin_amenity',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload)
        );
        $content = $response->getContent();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
        Auth::logout();        
    }

    /**
     *
     * @group amenity
     * @group create
     * @return null
     *
     */
    public function testCreateAmenityInexistentCustomer()
    {
        Auth::loginUsingId($this->test_cluster->id);
        $payload = $this->amenity_payload;
        try{
            $response = $this->call(
                'PUT',
                'unknown/amenities/amenity',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload)
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }

    /**
     *
     * @group amenity
     * @group create
     * @return null
     *
     */
    public function testCreateAmenityWrongCustomer()
    {
        Auth::loginUsingId($this->test_cluster->id);
        $payload = $this->amenity_payload;
        try{
            $response = $this->call(
                'PUT',
                'wrong/amenities/wrong_amenity',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload)
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }

    /**
     *
     * @group amenity
     * @group create
     * @return null
     *
     */
    public function testCreateAmenityWrongCredentials()
    {
        Auth::logout();
        $response = $this->call(
            'PUT',
            'test/amenities/wrong_amenity',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($this->amenity_payload)
        );
        $this->assertEquals($response->getStatusCode(), 401);
    }

    /**
     *
     * @group amenity
     * @group create
     * @return null
     *
     */
    public function testCreateMalformedAmenity()
    {
        $caught = false;
        Auth::loginUsingId($this->test_cluster->id);
        $payload = $this->amenity_payload;
        $payload['description'] = '';
        try {
            $response = $this->call(
                'PUT',
                'test/amenities/test_amenity',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload)
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        $payload = $this->amenity_payload;
        $payload['description'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/amenities/test_amenity',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload)
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        $payload = $this->amenity_payload;
        $payload['schema'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/amenities/test_amenity',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload)
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
        Auth::logout();
    }


    /**
     *
     * @group amenity
     * @group get
     * @return null
     *
     */
    public function testGetAmenities()
    {
        $response = $this->call(
            'GET',
            'test/amenities',
            array(),
            array(),
            ReservationTest::$headers,
            array(),
            false
        );        
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
        $this->assertInternalType('array', $data); 
    }

    /**
     *
     * @group amenity
     * @group get
     * @return null
     *
     */
    public function testGetAmenitiesWrongCustomer()
    {   
        try{
            $response = $this->call(
                'GET',
                'wrong/amenities',
                array(),
                array(),
                ReservationTest::$headers,
                array(),
                false
            );  
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }

    /**
     *
     * @group amenity
     * @group get
     * @return null
     *
     */
    public function testGetAmenity()
    {
        Auth::loginUsingId($this->test_cluster->id);
        $payload = $this->amenity_payload;
        $response = $this->call(
            'PUT',
            'test/amenities/get_amenity',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertNotNull(json_decode($response->getContent()));
        Auth::logout();

        $response = $this->call(
            'GET',
            'test/amenities/get_amenity',
            array(),
            array(),
            ReservationTest::$headers,
            array(),
            false
        );  
        $content = $response->getContent();
                $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
        $this->assertInternalType('object', $data);
    }

    /**
     *
     * @group amenity
     * @group get
     * @return null
     *
     */
    public function testGetAmenitiesNonExistentCustomer()
    {
        try{
            $response = $this->call(
                'GET',
                'unknown/amenities',
                array(),
                array(),
                ReservationTest::$headers,
                array(),
                false
            ); 
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }

    /**
     *
     * @group amenity
     * @group get
     * @return null
     *
     */
    public function testGetNonExistentAmenity()
    {
        try{
            $response = $this->call(
                'GET',
                'test/amenities/inexistent',
                array(),
                array(),
                ReservationTest::$headers,
                array(),
                false
            ); 
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }


    /**
     *
     * @group amenity
     * @group delete
     * @return null
     *
     */
    public function testDeleteAmenity()
    {
        Auth::loginUsingId($this->test_cluster->id);
        $payload = $this->amenity_payload;
        $response = $this->call(
            'PUT',
            'test/amenities/to_delete',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );
        $content = $response->getContent();
        $data = json_decode($content);

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);

        $response = $this->call(
            'DELETE',
            'test/amenities/to_delete',
            array(),
            array(),
            ReservationTest::$headers,
            array(),
            false
        );
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
    }

    /**
     *
     * @group amenity
     * @group delete
     * @return null
     *
     */
    public function testDeleteAmenityWrongCustomer()
    {
        Auth::loginUsingId($this->test_cluster->id);
        try{
            $response = $this->call(
                'DELETE',
                'test2/amenities/test_amenity',
                array(),
                array(),
                ReservationTest::$headers,
                array(),
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }

    /**
     *
     * @group amenity
     * @group delete
     * @return null
     *
     */
    public function testDeleteNonExistentAmenity()
    {
        Auth::loginUsingId($this->test_cluster->id);
        try{
            $response = $this->call(
                'DELETE',
                'test/amenities/inexistent',
                array(),
                array(),
                ReservationTest::$headers,
                array(),
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }        
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }


    /**
     *
     * @group entity
     * @group create
     * @return null
     *
     */
    public function testCreateEntity()
    {
        Auth::loginUsingId($this->test_cluster->id);
        $payload = $this->entity_payload;
        $payload['name'] = 'create_thing';
        $payload['body']['name'] = 'create_thing';
        $response = $this->call(
            'PUT',
            'test/things/create_thing',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
        Auth::logout();
    }

    /**
     *
     * @group entity
     * @group create
     * @return null
     */
    public function testCreateEntityAdmin()
    {
        Auth::loginUsingId($this->admin_cluster->id);
        $payload = $this->entity_payload;
        $payload['name'] = 'create_admin_thing';
        $payload['body']['name'] = 'create_admin_thing';
        $response = $this->call(
            'PUT',
            'test/things/create_admin_thing',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
        Auth::logout();
    }


    /**
     *
     * @group entity
     * @group create
     * @return null
     *
     */
    public function testCreateEntityWrongCustomer()
    {
        Auth::loginUsingId($this->test_cluster->id);
        $payload = $this->entity_payload;
        try{
            $response = $this->call(
                'PUT',
                'test2/things/new_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
                return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }

    /**
     *
     * @group entity
     * @group create
     * @return null
     *
     */
    public function testCreateEntityMalformedJson()
    {
        try {
            Auth::loginUsingId($this->test_cluster->id);
            $payload = $this->entity_payload;
            $payload['name'] = 'malformedjson_thing';
            $payload['body']['name'] = 'malformedjson_thing';
            $response = $this->call(
                'PUT',
                'test/things/malformedjson_thing',
                array(),
                array(),
                ReservationTest::$headers,
                '{"this" : {"is" : "malformed"}',
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) throw new Exception("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }

    /**
     *
     * @group entity
     * @group create
     * @return null
     *
     */
    public function testCreateEntityEmptyPayload()
    {
        try {
            Auth::loginUsingId($this->test_cluster->id);
            $response = $this->call(
                'PUT',
                'test/things/emptypayload_thing',
                array(),
                array(),
                ReservationTest::$headers,
                '',
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) throw new Exception("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }

    /**
     *
     * @group entity
     * @group update
     * @return null
     *
     */
    public function testUpdateExistingEntity()
    {
        Auth::loginUsingId($this->test_cluster->id);
        $payload = $this->entity_payload;
        $payload['name'] = 'existing_thing';
        $payload['body']['name'] = 'existing_thing';
        $response = $this->call(
            'PUT',
            'test/things/existing_thing',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);

        $response = $this->call(
            'PUT',
            'test/things/existing_thing',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
        Auth::logout();
    }

    
    /**
     *
     * @group entity
     * @group create
     * @return null
     *
     */
    public function testUpdateEntityEmptyPayload()
    {
        try {
            Auth::loginUsingId($this->test_cluster->id);
            $payload = $this->entity_payload;
            $payload['name'] = 'updateemptypayload_thing';
            $payload['body']['name'] = 'updateemptypayload_thing';
            $response = $this->call(
                'PUT',
                'test/things/updateemptypayload_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
            $content = $response->getContent();
            $data = json_decode($content);
            $this->assertEquals($response->getStatusCode(), 200);
            $this->assertJson($content);


            $response = $this->call(
                'PUT',
                'test/things/updateemptypayload_thing',
                array(),
                array(),
                ReservationTest::$headers,
                '',
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) throw new Exception("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }

    /**
     *
     * @group entity
     * @group create
     * @return null
     *
     */
    public function testUpdateEntityMalformedJson()
    {
        try {
            Auth::loginUsingId($this->test_cluster->id);
            $payload = $this->entity_payload;
            $payload['name'] = 'updatemalformedjson_thing';
            $payload['body']['name'] = 'updatemalformedjson_thing';
            $response = $this->call(
                'PUT',
                'test/things/updatemalformedjson_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
            $content = $response->getContent();
            $data = json_decode($content);
            $this->assertEquals($response->getStatusCode(), 200);
            $this->assertJson($content);


            $response = $this->call(
                'PUT',
                'test/things/updatemalformedjson_thing',
                array(),
                array(),
                ReservationTest::$headers,
                '{"this" : {"is" : "malformed"}',
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) throw new Exception("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }
    /**
     *
     * @group entity
     * @group create
     * @return null
     *
     */
    public function testCreateEntityMissingParameters()
    {
        $caught = false;

        Auth::loginUsingId($this->test_cluster->id);
        
        $payload = $this->entity_payload;
        $payload['type'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) throw new Exception("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['type'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['type'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['type'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['price'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['contact'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['contact'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['contact'] = 'not a url';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['support'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['support'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['support'] = 'not a url';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['opening_hours'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        unset($payload['body']['price']['daily']);
        unset($payload['body']['price']['hourly']);
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['price']['daily'] = -1;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['price']['currency'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['price']['currency'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
        

        $payload = $this->entity_payload;
        $payload['body']['price']['currency'] = 'pokethunes';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['opening_hours'] = array();
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['opening_hours'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['opening_hours'] = array();
        $opening_hour = array();
        $opening_hours['opens'] = array('09:00', '13:00');
        $opening_hours['closes'] = array('12:00', '17:00');
        $opening_hours['dayOfWeek'] = 1;
        $opening_hours['validFrom'] = date("Y-m-d h:m:s", time()+60*60*24);
        $opening_hours['validThrough'] =  date("Y-m-d h:m:s", time()+(365*24*60*60));
        array_push($this->entity_payload['body']['opening_hours'], $opening_hours);
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['map'] = null;
        try{
           $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['map'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['floor'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['floor'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['floor'] = 'not an int';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['building_name'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['building_name'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['map']['img'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['map']['img'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['map']['img'] = 'not a url';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['map']['reference'] = null;
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['location']['map']['reference'] = '';
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->entity_payload;
        $payload['body']['amenities'] = array('unknown amenities');
        try{
            $response = $this->call(
                'PUT',
                'test/things/missing_thing',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true; 
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
        
        Auth::logout();
    }

    /**
     *
     * @group entity
     * @group get
     * @return null
     *
     */
    public function testGetEntities()
    {   
        
        Auth::loginUsingId($this->test_cluster->id);

        $response = $this->call(
            'GET',
            'test/things',
            array(),
            array(),
            ReservationTest::$headers,
            array(),
            false
        );
        
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
        $this->assertInternalType('array', $data); 

        Auth::logout();
    }

    /**
     *
     * @group entity
     * @group get
     * @return null
     *
     */
    public function testGetEntity()
    {
        Auth::loginUsingId($this->test_cluster->id);
        
        $payload = $this->entity_payload;
        $payload['name'] = 'get_thing';
        $payload['body']['name'] = 'get_thing';
        
        $response = $this->call(
            'PUT',
            'test/things/get_thing',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );

        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
        
        Auth::logout();

        $response = $this->call(
            'GET',
            'test/things/get_thing',
            array(),
            array(),
            ReservationTest::$headers,
            array(),
            false
        );

        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
    }

    /**
     *
     * @group entity
     * @group get
     * @return null
     *
     */
    public function testGetEntityWrongCustomer()
    {
        try{ 
            $response = $this->call(
                'GET',
                'wrong/things/get_thing',
                array(),
                array(),
                ReservationTest::$headers,
                array(),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }

    /** 
     *
     * @group entity
     * @group get
     * @return null
     *
     */
    public function testGetNonExistentEntity()
    { 
        try{ 
            $response = $this->call(
                'GET',
                'test/things/unknown_thing',
                array(),
                array(),
                ReservationTest::$headers,
                array(),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }

    /**
     *
     * @group reservation
     * @group create
     * @return null
     *
     */
    public function testCreateReservation()
    {
        Auth::loginUsingId($this->test_cluster->id);
        
        $payload = $this->entity_payload;
        $payload['name'] = 'reservation_thing';
        $payload['body']['name'] = 'reservation_thing';
        $payload['body']['opening_hours'] = array();

        for($i=1; $i <= 7; $i++){
            $opening_hours = array();
            $opening_hours['opens'] = array('00:00');
            $opening_hours['closes'] = array('23:59');
            $opening_hours['dayOfWeek'] = $i;
            $opening_hours['validFrom'] = date("Y-m-d H:m:s", time()-365*24*60*60);
            $opening_hours['validThrough'] =  date("Y-m-d H:m:s", time()+(365*24*60*60));
            array_push($payload['body']['opening_hours'], $opening_hours);
        }

        $response = $this->call(
            'PUT',
            'test/things/reservation_thing',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );

        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);

        $payload = $this->reservation_payload;
        $payload['thing'] = 'http://this.is.a.url/' . $this->test_cluster->clustername . '/things/reservation_thing';
        $payload['type'] = 'room';
        $payload['time']['from'] = date('c', mktime(9, 0, 0, date('m'), date('d')+1, date('Y')));
        $payload['time']['to'] = date('c', mktime(10, 0, 0, date('m'), date('d')+1, date('Y')));
        
        $response = $this->call(
            'POST',
            'test/reservations',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );
        
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);

        Auth::logout();
    }

    /**
     *
     * @group reservation
     * @group create
     * @return null
     */
    public function testCreateReservationAdmin()
    {
        Auth::loginUsingId($this->admin_cluster->id);

        $payload = $this->entity_payload;
        $payload['name'] = 'admin_reservation_thing';
        $payload['body']['name'] = 'admin_reservation_thing';
        $payload['body']['opening_hours'] = array();

        for($i=1; $i <= 7; $i++){
            $opening_hours = array();
            $opening_hours['opens'] = array('00:00');
            $opening_hours['closes'] = array('23:59');
            $opening_hours['dayOfWeek'] = $i;
            $opening_hours['validFrom'] = date("Y-m-d H:m:s", time()-365*24*60*60);
            $opening_hours['validThrough'] =  date("Y-m-d H:m:s", time()+(365*24*60*60));
            array_push($payload['body']['opening_hours'], $opening_hours);
        }

        $response = $this->call(
            'PUT',
            'test/things/admin_reservation_thing',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );

        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);


        $payload = $this->reservation_payload;
        $payload['thing'] = 'http://this.is.a.url/' . $this->test_cluster->clustername . '/things/admin_reservation_thing';
        $payload['type'] = 'room';
        $payload['time']['from'] = date('c', mktime(9, 0, 0, date('m'), date('d')+1, date('Y')));
        $payload['time']['to'] = date('c', mktime(10, 0, 0, date('m'), date('d')+1, date('Y')));
        
        $response = $this->call(
            'POST',
            'test/reservations',
            array(),
            array(),
            ReservationTest::$headers,
            json_encode($payload),
            false
        );        
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);

        Auth::logout();
    }


    /**
     *
     * @group reservation
     * @return null
     *
     */
    public function testCreateReservationWrongCustomer()
    {
        Auth::loginUsingId($this->test_cluster->id);

        $payload = $this->reservation_payload;
        $payload['time']['from'] = time();
        $payload['time']['to'] = time() + (60*60*2);
        $payload['announce'] = array('yeri', 'pieter', 'nik', 'quentin');
        $payload['thing'] = 'http://this.is.a.url/' . $this->test_cluster->clustername . '/things/reservation_thing';
        $payload['type'] = 'room';

        try{
            $response = $this->call(
                'POST',
                'test2/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }

    /**
     *
     * @group reservation
     * @return null
     *
     */
    public function testCreateReservationInvalidJson()
    {
        $caught = false;

        Auth::loginUsingId($this->test_cluster->id);

        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                '"{this" : { "is" : "malformed"}',
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }

    /**
     *
     * @group reservation
     * @return null
     *
     */
    public function testCreateReservationEmptyPayload()
    {
        $caught = false;

        Auth::loginUsingId($this->test_cluster->id);
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                '',
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
    }
    /**
     *
     * @group reservation
     * @return null
     *
     */
    public function testCreateReservationMissingParameters()
    {
        $caught = false;

        Auth::loginUsingId($this->test_cluster->id);

        $payload = $this->reservation_payload;
        $payload['type'] = 'room';
        $payload['thing'] = '';

        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->reservation_payload;
        $payload['thing'] = null;

        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->reservation_payload;
        $payload['type'] = '';

        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
                $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->reservation_payload;
        $payload['type'] = null;

        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
            Auth::logout();
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
                $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->reservation_payload;
        $payload['time'] = null;

        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->reservation_payload;
        $payload['time']['from'] = null;

        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->reservation_payload;
        $payload['time']['from'] = -1;

        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['time']['from'] = time()-1;
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['time']['to'] = null;
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['time']['to'] = -1;
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['time']['to'] = time()-1;
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['time']['to'] = time();
        $payload['time']['from'] = $payload['time']['to'] - 1;
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");


        $payload = $this->reservation_payload;
        $payload['comment'] = '';
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['comment'] = null;
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['subject'] = '';
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['subject'] = null;
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['announce'] = null;
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");

        
        $payload = $this->reservation_payload;
        $payload['announce'] = '';
        
        try{
            $response = $this->call(
                'POST',
                'test/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                json_encode($payload),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\HttpException $e){
            $caught = true;
        }
        if(!$caught) $this->raise("Symfony\Component\HttpKernel\Exception\HttpException not raised.");
        
        Auth::logout();
    }

    
    //TODO : work on times to check validation
    /*public function testCreateAlreadyBookedReservation()
    {
        $payload = $this->entity_payload;
        ReservationTest::$headers = array('Accept' => 'application/json');
        $options = array('auth' => array('test', 'test'));

        $request = Requests::put(Config::get('app.url'). '/test/already_booked_entity', ReservationTest::$headers, $payload, $options);
        $this->assertEquals($request->status_code, 200);

        $payload = $this->reservation_payload;
        $payload['entity'] = 'already_booked_entity';
        $payload['type'] = 'room';
        $payload['time']['from'] = time()+(60*60);
        $payload['time']['to'] = $payload['time']['from'] + (60*60*2);

        $request = Requests::post(Config::get('app.url'). '/test/reservation', ReservationTest::$headers, $payload, $options);
        $this->assertEquals($request->status_code, 200);

        $request = Requests::post(Config::get('app.url'). '/test/reservation', ReservationTest::$headers, $payload, $options);
        $this->assertEquals($request->status_code, 400);



    }

    public function testCreateReservationOnUnavailableEntity()
    {

    }*/

    /**
     *
     * @group reservation
     * @return null
     */
    public function testGetReservations()
    {
        
        $response = $this->call(
            'GET',
            'test/reservations',
            array(),
            array(),
            ReservationTest::$headers,
            array(),
            false
        );
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);


        $params = array('day' => date('Y-m-d', time()));  
        $response = $this->call(
            'GET',
            'test/reservations',
            $params,
            array(),
            ReservationTest::$headers,
            array(),
            false
        );
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($content);
    }

    
    /**
     *
     * @group reservation
     * @return null
     */
    public function testGetReservation()
    {
        
        $response = $this->call(
            'GET',
            'test/reservations',
            array(),
            array(),
            ReservationTest::$headers,
            array(),
            false
        );
        $content = $response->getContent();
        $reservations = json_decode($content);

        foreach($reservations as $reservation){
                $response = $this->call(
                    'GET',
                    'test/reservations/'.$reservation->id,
                    array(),
                    array(),
                    ReservationTest::$headers,
                    array(),
                    false
                );
                $content = $response->getContent();
                $data = json_decode($content);
                $this->assertEquals($response->getStatusCode(), 200);
                $this->assertJson($content);
        }
    }


    /**
     *
     * @group reservation
     *
     * @return null
     */
    /*public function testUpdateReservation()
    {
        ReservationTest::$headers = array('Accept' => 'application/json');
        $options = array('auth' => array('admin', 'admin'));
        $request = Requests::get(Config::get('app.url'). '/test/reservation', ReservationTest::$headers);
        $reservations = json_decode($request->body);
        foreach($reservations as $reservation){
                //let say that we just change the reservation time.
                $payload = array(
                        'time' => array(
                                'from' => date('c', mktime(date('H', time())+3)),
                                'to' => date('c', mktime(date('H', time())+5))
                        ),
                        'subject' => 'updated subject',
                        'comment' => 'I think we just updated this reservation',
                        'announce' => array('yeri', 'pieter', 'nik', 'quentin', 'new member')
                );
                $request = Requests::post(Config::get('app.url'). '/test/reservation/'.$reservation->id, ReservationTest::$headers, 
                        $payload, $options);
                $this->assertEquals($request->status_code, 200);
                $this->assertNotNull(json_decode($request->body));
                $this->assertEquals(count(json_decode($request->body)), 1);

        }
    }*/


    /**
     *
     * @group reservation
     * @return null
     *
     */
    public function testGetReservationWrongCustomer()
    { 
        try{
            $response = $this->call(
                'GET',
                'wrong/reservations',
                array(),
                array(),
                ReservationTest::$headers,
                array(),
                false
            );
        }catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e){
            return;
        }
        $this->raise("Symfony\Component\HttpKernel\Exception\NotFoundHttpException not raised.");
    }
}


?>
