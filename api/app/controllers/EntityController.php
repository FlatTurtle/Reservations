<?php

class EntityController extends Controller {


    public function getEntities($customer_name) {
    		
        $customer = DB::table('customer')
        ->where('username', '=', $customer_name)
        ->first();

        if(isset($customer)){
			$db_entities = DB::table('entity')
            ->where('customer_id', '=', $customer->id)
            ->get();

			$entities = array();

			foreach($db_entities as $entity){
                if(isset($entity->body))
				    array_push($entities, json_decode($entity->body));
			}
			return json_encode($entities);
			
    	}else{
    		App::abort(404, 'Customer not found');
    	}
    }

    public function getAmenities($customer_name) {
        
        $customer = DB::table('customer')
        ->where('username', '=', $customer_name)
        ->first();

        if(isset($customer)){

            $db_amenities = DB::table('entity')
            ->where('customer_id', '=', $customer->id)
            ->where('type', '=', 'amenity')
            ->get();

            $amenities = array();

            foreach($db_amenities as $amenity){
                if(isset($amenity->body))
                    array_push($amenities, json_decode($amenity->body));                
            }
            return json_encode($amenities);
            
        }else{
            App::abort(404, 'Customer not found');
        }
    }

    public function getAmenityByName($customer_name, $name) {

    
        $customer = DB::table('customer')
        ->where('username', '=', $customer_name)
        ->first();

        if(isset($customer)){
            $amenity = DB::table('entity')
            ->where('customer_id', '=', $customer->id)
            ->where('type', '=', 'amenity')
            ->where('name', '=', $name)
            ->first();

            if(!isset($amenity)){
                App::abort(404, 'Amenity not found');
            }else{
                return $amenity->body;
            }
            
        }else{
            App::abort(404, 'Customer not found');
        }
    }


    public function getEntityByName($customer_name, $name) {

 
        $customer = DB::table('customer')
        ->where('username', '=', $customer_name)
        ->first();

        if(isset($customer)){

            $entity = DB::table('entity')
            ->where('customer_id', '=', $customer->id)
            ->where('name', '=', $name)
            ->first();

            if(!isset($entity)){
                App::abort(404, 'Entity not found');
            }else{
                return $entity->body;
            }
            
        }else{
            App::abort(404, 'Customer not found');
        }
    }

    public function createEntity($customer_name, $name) {

        $customer = DB::table('customer')
            ->where('username', '=', $customer_name)
            ->first();

        if(isset($customer)){

            /* we pass the basicauth so we can test against 
            this username with the url {customer_name}*/
            $username = Request::header('php-auth-user');

            if(!strcmp($customer_name, $username)){

                
                //TODO : custom error messages
                Validator::extend('body', function($attribute, $value, $parameters)
                {
                    if(!isset($value['name']) || count($value['name']) < 1)
                        return false;
                    //do we manage a supported types list ??
                    if(!isset($value['type']) || count($value['type']) < 1)
                        return false;

                    // opening hours validation

                    if(!isset($value['opening_hours']))
                        return false;

                    foreach($value['opening_hours'] as $opening_hour){

                        //TODO : test against current time value (after, before)
                        if(!isset($opening_hour['opens']))
                            return false;
                        if(!isset($opening_hour['closes']))
                            return false;
                        if(!isset($opening_hour['validFrom']))
                            return false;
                        if(!isset($opening_hour['validThrough']))
                            return false;
                        if(!isset($opening_hour['dayOfWeek']))
                            return false;
                        if($opening_hour['dayOfWeek'] < 1 || $opening_hour['dayOfWeek'] > 7)
                            return false;
                    }

                    //price validation

                    //TODO : lib to manage different currencies ? 
                    $currencies = array('dollar', 'euro', 'sterling', 'yen');
                    $groupings = array('hourly', 'daily', 'weekly', 'monthly', 'yearly');

                    if(!isset($value['price']))
                        return false;
                    if(!isset($value['price']['currency']))
                        return false;
                    if(!isset($value['price']['amount']))
                        return false;
                    if(!isset($value['price']['grouping']))
                        return false;

                    if($value['price']['amount'] < 0)
                        return false;
                    //TODO : test against groupings and currencies


                    if(!isset($value['description']) || count($value['description']) < 1)
                        return false;
                    

                    //location validation

                    if(!isset($value['location']))
                        return false;
                    if(!isset($value['location']['map']))
                        return false;
                    if(!isset($value['location']['floor']))
                        return false;
                    if(!isset($value['location']['building_name']) 
                        || count($value['location']['building_name']) < 1)
                        return false;

                    //location map validation

                    if(!isset($value['location']['map']['img'])
                        || count($value['location']['map']['img']) < 1)
                        return false;
                    if(!isset($value['location']['map']['reference'])
                        || count($value['location']['map']['reference']) < 1)
                        return false;


                    //TODO : test values with url regex
                    if(!isset($value['contact']) || count($value['contact']) < 1)
                        return false;
                    if(!isset($value['support']) || count($value['support']) < 1)
                        return false;
                    //TODO : typeof against amenities array ?

                    return true;
                });

                //TODO : create a validator object depending on type parameter
                $room_validator = Validator::make(
                    Input::all(),
                    array(
                        'name' => 'required',
                        'type' => 'required|alpha_dash',
                        'body' => 'required|body'
                    )
                );


                if (!$room_validator->fails()) {

                    //TODO : check if amenities in $data['amenities'] exists in DB
                    DB::table('entity')->insert(
                        array(
                            'name' => Input::get('name'),
                            'type' => Input::get('type'),
                            'updated_at' => microtime(),
                            'created_at' => microtime(),
                            'body' => json_encode(Input::get('body')),
                            'customer_id' => $customer->id
                        )
                    );

                    
                } else {
                    $messages = $room_validator->messages();
                    
                    $s = "JSON does not validate. Violations:\n";
                    foreach ($messages->all() as $message)
                    {
                        $s .= "$message\n";
                    }
                    App::abort(400, $s);
                } 
            }else{
                App::abort(400, "You can't modify entities from another customer");
            }
        }else{
            App::abort(404, 'Customer not found');
        }   
    }

    public function createAmenity($customer_name, $name) {


        $customer = DB::table('customer')
            ->where('username', '=', $customer_name)
            ->first();
        if(isset($customer)){
            
            /* we pass the basicauth so we can test against 
            this username with the url {customer_name}*/
            $username = Request::header('php-auth-user');
            if(!strcmp($customer_name, $username)){

                //TODO : custom error messages
                Validator::extend('amenity', function($attribute, $value, $parameters)
                {
                    // $value is a json schema, we need to validate it

                    return true;
                });
                $amenity_validator = Validator::make(
                    Input::all(),
                    array(
                        'name' => 'required',
                        'type' => 'required|same:amenity',
                        'schema' => 'amenity'
                    )
                );

                $entity_json = Input::all();

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
            }else{
                App::abort(400, "You are not allowed to modify amenities from another customer");
            }
            
        }else{
            App::abort(404, 'Customer not found');
        }               
    }

    public function deleteAmenity($customer_name, $name) {
        
        
        $customer = DB::table('customer')
            ->where('username', '=', $customer_name)
            ->first();
        if(isset($customer)){

            /* we pass the basicauth so we can test against 
            this username with the url {customer_name}*/
            $username = Request::header('php-auth-user');

            if(!strcmp($customer_name, $username)){
            
                DB::table('entity')
                ->where('customer_id', '=', $customer->id)
                ->where('name', '=', $name)
                ->delete();
            }else{
                App::abort(400, "You're not allowed to delete amenities from another customer");
            }
            
        }else{
            App::abort(404, 'Customer not found');
        }
    }
}


?>