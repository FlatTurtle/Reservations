<?php
use JustinRainbow\JsonSchema\Validator;

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


                //TODO : create a validator object depending on type parameter
                $room_validator = EntityValidator::make(
                    array(
                        'name' => 'required',
                        'type' => 'required|room',
                        'body' => 'required'
                    );
                );


                $entity_json = Input::json();

                //TODO check if type exists in json
                $type = $entity_json->get('type');
                $body = $entity_json->all();
                // Get the schema and data as objects
                /*$retriever = new JsonSchema\Uri\UriRetriever();

                //TODO : check if schema exists
                $schema = $retriever->retrieve('file://' . realpath('schemas/'.$type.'.json'));
                

                // If you use $ref or if you are unsure, resolve those references here
                // This modifies the $schema object
                $refResolver = new JsonSchema\RefResolver($retriever);
                $refResolver->resolve($schema, 'file://' . __DIR__);

                // Validate
                $validator = new JsonSchema\Validator();
                $validator->check($body, $schema);

                if ($validator->isValid()) {*/

                    //TODO : check if amenities in $data['amenities'] exists in DB
                    DB::table('entity')->insert(
                        array(
                            'name' => $name,
                            'type' => $type,
                            'updated_at' => microtime(),
                            'created_at' => microtime(),
                            'body' => json_encode($body),
                            'customer_id' => $customer->id
                        )
                    );

                    
                /*} else {
                    $s = "JSON does not validate. Violations:\n";
                    foreach ($validator->getErrors() as $error) {
                        $s .= sprintf("[%s] %s\n", $error['property'], $error['message']);
                    }
                    App::abort(400, $s);
                } */
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

                $amenity_validator = AmenityValidator::make(
                    array(
                        'name' => 'required',
                        'type' => 'required|room',
                        'body' => 'required'
                    );
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