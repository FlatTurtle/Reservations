<?php

class EntityController extends Controller {

   
    private function sendErrorMessage($validator){
        $messages = $validator->messages();
        $s = "JSON does not validate. Violations:\n";
        foreach ($messages->all() as $message)
        {
            $s .= "$message\n";
        }
        App::abort(400, $s);
    }

    public function getEntities($user_name) {
    		
        $user = DB::table('user')
        ->where('username', '=', $user_name)
        ->first();

        if(isset($user)){
			$db_entities = DB::table('entity')
            ->where('user_id', '=', $user->id)
            ->get();

			$entities = array();

			foreach($db_entities as $entity){
                if(isset($entity->body))
				    array_push($entities, json_decode($entity->body));
			}
			return json_encode($entities);
			
    	}else{
    		App::abort(404, 'user not found');
    	}
    }

    public function getAmenities($user_name) {
        
        $user = DB::table('user')
        ->where('username', '=', $user_name)
        ->first();

        if(isset($user)){

            $db_amenities = DB::table('entity')
            ->where('user_id', '=', $user->id)
            ->where('type', '=', 'amenity')
            ->get();

            $amenities = array();

            foreach($db_amenities as $amenity){
                if(isset($amenity->body))
                    array_push($amenities, json_decode($amenity->body));                
            }
            return json_encode($amenities);
            
        }else{
            App::abort(404, 'user not found');
        }
    }

    public function getAmenityByName($user_name, $name) {

    
        $user = DB::table('user')
        ->where('username', '=', $user_name)
        ->first();

        if(isset($user)){
            $amenity = DB::table('entity')
            ->where('user_id', '=', $user->id)
            ->where('type', '=', 'amenity')
            ->where('name', '=', $name)
            ->first();

            if(!isset($amenity)){
                App::abort(404, 'Amenity not found');
            }else{
                return $amenity->body;
            }
            
        }else{
            App::abort(404, 'user not found');
        }
    }


    public function getEntityByName($user_name, $name) {

 
        $user = DB::table('user')
        ->where('username', '=', $user_name)
        ->first();

        if(isset($user)){

            $entity = DB::table('entity')
            ->where('user_id', '=', $user->id)
            ->where('name', '=', $name)
            ->first();

            if(!isset($entity)){
                App::abort(404, 'Entity not found');
            }else{
                return $entity->body;
            }
            
        }else{
            App::abort(404, 'user not found');
        }
    }

    public function createEntity($user_name, $name) {

        $user = DB::table('user')
            ->where('username', '=', $user_name)
            ->first();

        if(isset($user)){

            /* we pass the basicauth so we can test against 
            this username with the url {user_name}*/
            $username = Request::header('php-auth-user');

            if(!strcmp($user_name, $username)){

                $exist = DB::table('entity')->where('name', '=', $name)->get();
                if($exist){
                    App::abort(400, 'An entity with this name already exist.');
                }else{
                    // Validation building ...
                    Validator::extend('opening_hours', function($attribute, $value, $parameters)
                    {
                        $now = date("Y-m-d h:m:s", time());
                        foreach($value as $opening_hour){
                            $opening_hour_validator = Validator::make(
                                $opening_hour,
                                array(
                                    'opens' => 'required',
                                    'closes' => 'required',
                                    'validFrom' => 'required',
                                    'validThrough' => 'required|after:'.$now,
                                    'dayOfWeek' => 'required|numeric|between:0,7'
                                )
                            );
                            if($opening_hour_validator->fails())
                                $this->sendErrorMessage($opening_hour_validator);
                        }
                        return true;
                    });


                    Validator::extend('price', function($attribute, $value, $parameters)
                    {
                        $timings = array('hourly', 'daily', 'weekly', 'monthly', 'yearly');
                        $ISO4217 = array("AED","AFN","ALL","AMD","ANG","AOA","ARS","AUD","AWG","AZN","BAM","BBD","BDT",
"BGN","BHD","BIF","BMD","BND","BOB","BOV","BRL","BSD","BTN","BWP","BYR","BZD","CAD","CDF","CHE","CHF","CHW",
"CLF","CLP","CNY","COP","COU","CRC","CUC","CUP","CVE","CZK","DJF","DKK","DOP","DZD","EGP","ERN","ETB","EUR",
"FJD","FKP","GBP","GEL","GHS","GIP","GMD","GNF","GTQ","GYD","HKD","HNL","HRK","HTG","HUF","IDR","ILS","INR",
"IQD","IRR","ISK","JMD","JOD","JPY","KES","KGS","KHR","KMF","KPW","KRW","KWD","KYD","KZT","LAK","LBP","LKR",
"LRD","LSL","LTL","LVL","LYD","MAD","MDL","MGA","MKD","MMK","MNT","MOP","MRO","MUR","MVR","MWK","MXN","MXV",
"MYR","MZN","NAD","NGN","NIO","NOK","NPR","NZD","OMR","PAB","PEN","PGK","PHP","PKR","PLN","PYG","QAR","RON",
"RSD","RUB","RWF","SAR","SBD","SCR","SDG","SEK","SGD","SHP","SLL","SOS","SRD","SSP","STD","SVC","SYP","SZL",
"THB","TJS","TMT","TND","TOP","TRY","TTD","TWD","TZS","UAH","UGX","USD","USN","USS","UYI","UYU","UZS","VEF",
"VND","VUV","WST","XAF","XAG","XAU","XBA","XBB","XBC","XBD","XCD","XDR","XFU","XOF","XPD","XPF","XPT","XSU",
"XTS","XUA","XXX","YER","ZAR","ZMW","ZWL");

                        $intersect = array_intersect($timings, array_keys($value));
                        foreach($intersect as $index){
                            if($value[$index] < 0)
                                return false;
                        }
                        return (isset($value['currency']) && in_array($value['currency'], $ISO4217) && 
                            !empty($intersect));
                    });

                    Validator::extend('map', function($attribute, $value, $parameters)
                    {
                        $map_validator = Validator::make(
                            $value,
                            array(
                                'img' => 'required|url',
                                'reference' => 'required'
                            )
                        );
                        if($map_validator->fails())
                            $this->sendErrorMessage($map_validator);
                        return true;
                    });
                    Validator::extend('location', function($attribute, $value, $parameters)
                    {
                        $location_validator = Validator::make(
                            $value,
                            array(
                                'map' => 'required|map',
                                'floor' => 'required|numeric',
                                'building_name' => 'required'
                            )
                        );
                        if($location_validator->fails())
                            $this->sendErrorMessage($location_validator);
                        return true;
                    });

                    Validator::extend('amenities', function($attribute, $value, $parameters)
                    {
                        $present = true;
                        if(count($value)){
                            //we check if amenities provided as input exists in database
                            $amenities = DB::table('entity')->where('type', '=', 'amenity')->lists('name');
                            foreach($value as $amenity){
                                $present = in_array($amenity, $amenities);
                            }
                        }
                        return $present;
                    });

                    //TODO : custom error messages
                    Validator::extend('body', function($attribute, $value, $parameters)
                    {
                        $body_validator = Validator::make(
                            $value,
                            array(
                                'type' => 'required',
                                'description' => 'required',
                                'location' => 'required|location',
                                'price' => 'required|price',
                                'amenities' => 'amenities',
                                'contact' => 'required|url',
                                'support' => 'required|url',
                                'opening_hours' => 'required|opening_hours'
                            )
                        );

                        if($body_validator->fails())
                            $this->sendErrorMessage($body_validator);
                        return true;
                    });

                    $room_validator = Validator::make(
                        Input::all(),
                        array(
                            'type' => 'required|alpha_dash',
                            'body' => 'required|body'
                        )
                    );


                    // Validator testing
                    if (!$room_validator->fails()) {
                        $body = Input::get('body');
                        $body['name'] = $name;
                        DB::table('entity')->insert(
                            array(
                                'name' => $name,
                                'type' => Input::get('type'),
                                'updated_at' => microtime(),
                                'created_at' => microtime(),
                                'body' => json_encode($body),
                                'user_id' => $user->id
                            )
                        );
                    } else {
                        $this->sendErrorMessage($room_validator);
                    } 
                }
            }else{
               App::abort(403, "You can't modify entities from another user");
            }
        }else{
            App::abort(404, 'user not found');
        }   
    }


    


    public function createAmenity($user_name, $name) {

        Validator::extend('schema_type', function($attribute, $value, $parameters)
        {
            $supported_types = array('array', 'boolean', 'integer', 'number', 
                        'null', 'object', 'string');
            return in_array($value, $supported_types);
        });

        function validateProperty($property) {

            $property_validator = Validator::make($property,
                array(
                    'description' => 'required',
                    'type' => 'required|schema_type'
                )
            );
            if($property_validator->fails())
                $this->sendErrorMessage($property_validator);
            if(isset($property['properties']))
                validateProperty($property['properties']);
        }

        $user = DB::table('user')
            ->where('username', '=', $user_name)
            ->first();
        if(isset($user)){
            
            /* we pass the basicauth so we can test against 
            this username with the url {user_name}*/
            $username = Request::header('php-auth-user');
            if(!strcmp($user_name, $username)){

                //TODO : custom error messages
                Validator::extend('schema', function($attribute, $value, $parameters)
                {
                    $json = json_encode($value);
                    if($json != null){
                        
                        $schema_validator = Validator::make($value,
                            array(
                                '$schema' => 'required|url',
                                'title' => 'required',
                                'description' => 'required',
                                'type' => 'required|schema_type',
                                'properties' => 'required',
                                'required' => 'required'
                            )
                        );
                        if(!$schema_validator->fails()){
                            
                            foreach($value['required'] as $required){
                                if(!isset($value['properties'][$required]))
                                    return false;
                            }
                            foreach($value['properties'] as $property){
                                validateProperty($property);
                            }


                        }else{
                            $this->sendErrorMessage($schema_validator);
                        }
                    }else{
                        return false;
                    }
                    return true;
                });

                $exist = DB::table('entity')->where('name', '=', $name)->get();
                if($exist){
                    App::abort(400, 'An amenity with this name already exist.');
                }else{
                    $amenity_validator = Validator::make(
                        Input::all(),
                        array(
                            'description' => 'required',
                            'schema' => 'required|schema'
                        )
                    );

                    if(!$amenity_validator->fails()){

                        DB::table('entity')->insert(
                            array(
                                'name' => $name,
                                'type' => 'amenity',
                                'updated_at' => time(),
                                'created_at' => time(),
                                'body' => json_encode(Input::get('schema')),
                                'user_id' => $user->id
                            )
                        );
                    }else{
                        $this->sendErrorMessage($amenity_validator);
                    }
                }
                
            }else{
                App::abort(403, "You are not allowed to modify amenities from another user");
            }
            
        }else{
            App::abort(404, 'user not found');
        }               
    }

    public function deleteAmenity($user_name, $name) {
        
        
        $user = DB::table('user')
            ->where('username', '=', $user_name)
            ->first();
        print_r($user);
        if(isset($user)){

            /* we pass the basicauth so we can test against 
            this username with the url {user_name}*/
            $username = Request::header('php-auth-user');

            if(!strcmp($user_name, $username)){
                $amenity = DB::table('entity')
                ->where('user_id', '=', $user->id)
                ->where('type', '=', 'amenity')
                ->where('name', '=', $name);

                if($amenity->get() != null)
                    $amenity->delete();
                else
                    App::abort(404, 'Amenity not found');
            }else{
                App::abort(403, "You're not allowed to delete amenities from another user");
            }
            
        }else{
            App::abort(404, 'user not found');
        }
    }
}


?>