<?php


class ReservationController extends Controller {



	public function getReservations($customer_name) {
    	
    	$customer = DB::table('customer')
            ->where('username', '=', $customer_name)
            ->first();

    	if(isset($customer)){
    		
			$reservations = DB::table('reservation')
			->where('customer_id', '=', $customer->id)
			->where('from', '>', mktime(0,0,0))
			->get();
		
			return json_encode($reservations);
			
    	}else{
    		App::abort(404, 'Customer not found');
    	}
	}


	public function createReservation($customer_name){

	
    	$customer = DB::table('customer')
            ->where('username', '=', $customer_name)
            ->first();

    	if(isset($customer)){

			/* we pass the basicauth so we can compare 
			this username with the url {customer_name}*/
			
    		$username = Request::header('php-auth-user');
    		
    		if(!strcmp($customer_name, $username)){
    		
    			Validator::extend('type', function($attribute, $value, $parameters)
                {
                    // simply test if we support this type
                	if(!isset($value))
                		return false;
                	$types = array('room', 'amenity');
                	//TODO : php function like the 'in' in python
                    return true;
                });

                Validator::extend('time', function($attribute, $value, $parameters)
                {
                    if(!isset($value['from']))
                    	return false;
                    if(!isset($value['to']))
                    	return false;
                    if(int($value['from']) < mktime())
                    	return false;
                    if(int($value['to']) < mktime())
                    	return false;
                    //TODO : do we define a minimum reservation time between from and to ?
                    if(int($value['to']) < int($value['from']))
                    	return false;
                });

            
    			$reservation_validator = ReservationValidator::make(
    				Input::all(),
    				array(
    					'entity' => 'required',
    					'type' => 'required|type',
    					'time' => 'required|time',
    					'comment' => 'required',
    					'subject' => 'required',
    					'announce' => 'required'
                    )
    			);


				$reservation_json = Input::all();

				// Get the schema and data as objects
				
				$entity = DB::table('entity')
				->where('name', '=', $reservation_json['entity'])
				->where('customer_id', '=', $customer->id)->get();
				
				if($entity==null){
					App::abort(404, "Entity not found");
				}else{
					$reservation = DB::table('reservation')
					->where('from', '>=', $reservation_json['from'])
					->where('to', '<=', $reservation_json['to'])
					->where('entity_id', '=', $entity->id);
					if($reservation!=null){
						App::abort(400, 'The entity is already reserved at that time');
					}else{
						DB::table('reservation')->insert(
							array(
								'type' => $data['type'],
								'from' => $data['time']['from'],
								'to' => $data['time']['to'],
								'comment' => $data['comment'],
								'subject' => $data['subject'],
								'announce' => $data['announce'],
								'entity_id' => $entity->id,
								'customer_id' => $customer->id
							)
						);
					}
				}
			}else{
				App::abort(400, "You are not allowed to make reservations for another customer");
			}
    	}else{
    		App::abort(404, 'Customer not found');
    	}
	}
}


?>