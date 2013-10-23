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

	public function getReservations($user_name) {
    	
    	$user = DB::table('user')
            ->where('username', '=', $user_name)
            ->first();

    	if(isset($user)){
    		
    		if(Input::get('day')!=null){
    			$reservations = DB::table('reservation')
				->where('user_id', '=', $user->id)
				->where('from', '>=', strtotime(Input::get('day')))
				->where('from', '<=', strtotime(Input::get('day')))
				->get();
    		}else{
    			$reservations = DB::table('reservation')
				->where('user_id', '=', $user->id)
				->get();
    		}
    		foreach($reservations as $reservation){
    			$reservation->announce = json_decode($reservation->announce);
    		}
			return json_encode($reservations);
			
    	}else{
    		App::abort(404, 'user not found');
    	}
	}


	private function isAvailable($opening_hours, $reservation_time) {

		$from = strtotime($reservation_time['from']);
		$to = strtotime($reservation_time['to']);
		$available = false;
		foreach($opening_hours as $opening_hour){
			
			if($from < strtotime($opening_hour->validFrom))
				return false;
			if($to > strtotime($opening_hour->validThrough))
				return false;

			/* 
			 * We do not support reservation that goes on multiple days,
			 * if a user wants to book an entity on multiple days he had to
			 * do reservations for each day
			 */
			if($opening_hour->dayOfWeek == date('N', $from)
				&& $opening_hour->dayOfWeek == date('N', $to)){
				$i=0;
				foreach(array_combine($opening_hour->opens, $opening_hour->closes) as $open => $close){
					
					if(strtotime(date('Y-m-d H:m', $from)) >=
						strtotime(date('Y-m-d', $from) . $open))
						$i++;
					if(strtotime(date('Y-m-d H:m', $from)) <
						strtotime(date('Y-m-d', $from) . $close))
						$i--;
					if(strtotime(date('Y-m-d H:m', $to)) >
						strtotime(date('Y-m-d', $to) . $open))
						$i++;
					if(strtotime(date('Y-m-d H:m', $to)) <=
						strtotime(date('Y-m-d', $to) . $close))
						$i--;
				}
				if(!$i){$available=true;}
			}
		}
		return $available;
	}

	private function assertISO8601Date($date) {
		if (preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $date) > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function createReservation($user_name){

	
    	$user = DB::table('user')
            ->where('username', '=', $user_name)
            ->first();
    	if(isset($user)){

			/* we pass the basicauth so we can compare 
			this username with the url {user_name}*/
			
    		$username = Request::header('php-auth-user');
    		$client = User::where('username', '=', $username)->first();
    		if(!strcmp($user_name, $username) || $client->isAdmin()){
    		
    			Validator::extend('type', function($attribute, $value, $parameters)
                {
                	$types = array('room', 'amenity');
                    return in_array($value, $types);
                });

                Validator::extend('time', function($attribute, $value, $parameters)
                {
                	if(!isset($value['from']) || !isset($value['to']))
                		return false;
                	//check against ISO8601 regex
                	if(!$this->assertISO8601Date($value['from']))
                		return false;
                	if(!$this->assertISO8601Date($value['to']))
                		return false;

                	$from=strtotime($value['from']);
                	$to=strtotime($value['to']);

                	if(!$from || !$to)
                		return false;
                
                	if($from < (time()-Config::get('app.reservation_time_span')))
                		return false;
                	if ($to < (time() - Config::get('app.reservation_time_span')))
                		return false;
                	if ($to < $from)
                		return false;
                	if (($to-$from) < Config::get('app.reservation_time_span'))
                		return false;
                	return true;
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
					->where('user_id', '=', $user->id)->first();
					
					if($entity==null){
						App::abort(404, "Entity not found");
					}else{
						if($this->isAvailable(json_decode($entity->body)->opening_hours, 
							Input::get('time'))){

							$reservation = DB::table('reservation')
							->where('from', '>=', Input::get('time')['from'])
							->where('to', '<=', Input::get('time')['to'])
							->where('entity_id', '=', $entity->id)
							->first();

							if($reservation!=null){
								App::abort(400, 'The entity is already reserved at that time');
							}else{
								//we json encode the announce array for the sake of simplicity
								DB::table('reservation')->insert(
									array(
										'from' => Input::get('time')['from'],
										'to' => Input::get('time')['to'],
										'comment' => Input::get('comment'),
										'subject' => Input::get('subject'),
										'announce' => json_encode(Input::get('announce')),
										'entity_id' => $entity->id,
										'user_id' => $user->id
									)
								);
							}
						}else{
							App::abort(400, 'The entity is not available at that time');
						}
						
					}
    			}else{
    				$this->sendErrorMessage($reservation_validator);
    			}
				
				
			}else{
				App::abort(403, "You are not allowed to make reservations for another user");
			}
    	}else{
    		App::abort(404, 'user not found');
    	}
	}
}


?>