<?php


class ReservationController extends Controller {


	private function sendErrorMessage($validator){
        $messages = $validator->messages();
        $s = "JSON does not validate. Violations:\n";
        foreach ($messages->all() as $message)
        {
            $s .= "$message\n";
        }
        App::abort(400, $s);
    }

	public function getReservations($customer_name) {
    	
    	$customer = DB::table('customer')
            ->where('username', '=', $customer_name)
            ->first();

    	if(isset($customer)){
    		
    		if(Input::get('day')!=null){
    			$reservations = DB::table('reservation')
				->where('customer_id', '=', $customer->id)
				->where('from', '>=', strtotime(Input::get('day')))
				->where('from', '<=', strtotime(Input::get('day')))
				->get();
    		}else{
    			$reservations = DB::table('reservation')
				->where('customer_id', '=', $customer->id)
				->get();
    		}
			return json_encode($reservations);
			
    	}else{
    		App::abort(404, 'Customer not found');
    	}
	}


	private function isAvailable($opening_hours, $resevation_time) {

		$ok = false;
		foreach($opening_hours as $opening_hour){
			if($opening_hour['validFrom'] > $reservation['from'])
				return false;
			if($opening_hour['validThrough'] < $reservation['to'])
				return false;

			if($opening_hour['dayOfWeek'] == date('N', $reservation_time['from'])){
				foreach(array_combine($opening_hour['opens'], $opening_hour['closes']) as $open => $close){
					if(strtotime(date('Y-m-d h:m', $reservation_time['from'])) < 
						strtotime(date('Y-m-d', $reservation_time['from']) . $open))
						$ok = true;
				}
			}
			if($opening_hour['dayOfWeek'] == date('N', $reservation_time['to'])){
				foreach(array_combine($opening_hour['opens'], $opening_hour['closes']) as $open => $close){
					if(strtotime(date('Y-m-d h:m', $reservation_time['to'])) < 
						strtotime(date('Y-m-d', $reservation_time['to']) . $close))
						$ok = true;
				}
			}
		}
		return ok;
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
                	//TODO : manage supported types in database ?
                	$types = array('room', 'amenity');
                    return in_array($value, $types);
                });

                Validator::extend('time', function($attribute, $value, $parameters)
                {

                    if(!isset($value['from']))
                    	return false;
                    if(!isset($value['to']))
                    	return false;
                    if(!is_int($value['from']))
                    	return false;
                    if(!is_int($value['to']))
                    	return false;
                    if($value['from'] < time())
                    	return false;
                    if($value['to'] < time())
                    	return false;
                    //TODO : do we define a minimum reservation time between from and to ?
                    if($value['to'] < $value['from'])
                    	return false;
                });

            
    			$reservation_validator = Validator::make(
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


    			if(!$reservation_validator->fails()){
    				$entity = DB::table('entity')
					->where('name', '=', Input::get('entity'))
					->where('type', '=', Input::get('type'))
					->where('customer_id', '=', $customer->id)->get();
					
					if($entity==null){
						App::abort(404, "Entity not found");
					}else{
						if($this->isAvailable(json_decode($entity->body)['opening_hours'], 
							Input::get('time'))){
							$reservation = DB::table('reservation')
							->where('from', '>=', Input::get('time')['from'])
							->where('to', '<=', Input::get('time')['to'])
							->where('entity_id', '=', $entity->id);

							if($reservation!=null){
								App::abort(400, 'The entity is already reserved at that time');
							}else{
								//check if it's open
								DB::table('reservation')->insert(
									array(
										'type' => Input::get('type'),
										'from' => Input::get('time')['from'],
										'to' => Input::get('time')['to'],
										'comment' => Input::get('comment'),
										'subject' => Input::get('subject'),
										'announce' => Input::get('announce'),
										'entity_id' => $entity->id,
										'customer_id' => $customer->id
									)
								);
							}
						}
						
					}
    			}else{
    				$this->sendErrorMessage($reservation_validator);
    			}
				
				
			}else{
				App::abort(403, "You are not allowed to make reservations for another customer");
			}
    	}else{
    		App::abort(404, 'Customer not found');
    	}
	}
}


?>