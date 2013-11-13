<?php
/**
 *
 *
 */

/**
 * This class take care of everything related to reservations 
 * (create / update / delete).
 */
class ReservationController extends Controller
{


    /**
     * This function throw an error 400 and provide error messages
     * from validator $validator in JSON.
     *
     * @param validator : a Laravel validator
     * @return
     */
    private function _sendErrorMessage($validator)
    {
        $messages = $validator->messages();
        $s = "JSON does not validate. Violations:\n";
        foreach ($messages->all() as $message) {
            $s .= "$message\n";
        }
        App::abort(400, $s);
    }

    /**
     * Return a list of reservations that the user has made for the current day.
     * Day can be change by providing a 'day' as GET parameter.
     *
     * @param user_name : the user's name
     * @return 
     */ 
    public function getReservations($user_name)
    {
        $user = User::where('username', '=', $user_name)->first();

        if (isset($user)) {
            /*  Announce value is json encoded in db so we first retrieve 
                reservations from db, decode announce json and return 
                reservations to the user */
            if (Input::get('day')!=null) {
                $day = strtotime(Input::get('day'));    
                $_reservations = Reservation::where('user_id', '=', $user->id)
                ->where('from', '>=', $day)
                ->where('from', '<=', $day)
                ->get()
                ->toArray();
            }else{
                $_reservations = Reservation::where('user_id', '=', $user->id)->get()->toArray();
            }
            //FIXME : return entity name instead of id ?
            $reservations = array();
            foreach($_reservations as $reservation){
                $reservation['announce'] = json_decode($reservation['announce']);
                array_push($reservations, $reservation);
            }
            return Response::json($reservations);

        }else{
          App::abort(404, 'user not found');
        }
    }

    /**
     * Return a the reservation that has id $id.
     * @param user_name : the user's name
     * @param id : the id of the reservation to be deleted
     */
    public function getReservation($user_name, $id)
    {
        $user = User::where('username', '=', $user_name)->first();
        if (isset($user)) {
            $reservation = Reservation::find($id);
            if(isset($reservation)) {
                return Response::json($reservation);
            } else {
                App::abort(404, 'Reservation not found.');
            }
        } else {
            App::abort(404, 'user not found');
        }
    }

        

    /**
     * Verify if a room is available by checking its $opening_hours
     * agains the $reservation_time.
     * @param opening_hours : the room's opening hours
     * @param reservation_time : the reservation's time (from & to)
     */
    private function isAvailable($opening_hours, $reservation_time)
    {

        $from = strtotime($reservation_time['from']);
        $to = strtotime($reservation_time['to']);
        $available = false;
        foreach ($opening_hours as $opening_hour) {
            if ($from < strtotime($opening_hour->validFrom)) {
                return false;
            }
            if ($to > strtotime($opening_hour->validThrough)) {
                return false;
            }

            /* 
             * We do not support reservation that goes on multiple days,
             * if a user wants to book an entity on multiple days he had to
             * do reservations for each day
             */

            //compare dayOfWeek with the day value of $from and $to
            if ($opening_hour->dayOfWeek == date('N', $from)
                && $opening_hour->dayOfWeek == date('N', $to)
            ) {
                $i=0;
                foreach (array_combine($opening_hour->opens, $opening_hour->closes) 
                  as $open => $close) {
                    /* open an close values are formatted as H:m and dayOfWeek 
                        is the same so we compare timestamp between $from, $to,
                        $open and $close and the same day. */
                    if (strtotime(date('Y-m-d H:m', $from)) >=
                        strtotime(date('Y-m-d', $from) . $open)
                    )
                    $i++;
                    if (
                      strtotime(date('Y-m-d H:m', $from)) < strtotime(date('Y-m-d', $from) . $close)
                    )
                        $i--;
                    if (
                      strtotime(date('Y-m-d H:m', $to)) > strtotime(date('Y-m-d', $to) . $open)
                    )
                        $i++;
                    if (
                      strtotime(date('Y-m-d H:m', $to)) <= strtotime(date('Y-m-d', $to) . $close)
                    )
                    $i--;
                }
                if (!$i) $available=true;
            }
        }
        return $available;
    }

    /**
     * Check if a date is a valid ISO8601 formatted date.
     * @param $date : the date to check
     */
    private function isValidISO8601($date) {
        return preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $date) > 0 ;
    }

    /**
     * Create a new reservation for a authenticated user.
     * @param $user_name : user's name from url.
     *
     */
    public function createReservation($user_name){

        
        $user = User::where('username', '=', $user_name)->first();
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
                                      if(!$this->isValidISO8601($value['from']))
                                          return false;
                                      if(!$this->isValidISO8601($value['to']))
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

                    $entity = Entity::where('name', '=', Input::get('entity'))
                        ->where('type', '=', Input::get('type'))
                        ->where('user_id', '=', $user->id)->first();
                                        
                    if(!isset($entity)){
                        App::abort(404, "Entity not found");
                    }else{
                        $time = Input::get('time');
                        if($this->isAvailable(json_decode($entity->body)->opening_hours, $time)){

                            //FIXME
                            $from = date("U",strtotime($time['from']));
                            $to = date("U",strtotime($time['to']));

                            $reservation = Reservation::where('from', '>=', $from)->where('to', '<=', $to)->where('entity_id', '=', $entity->id)->first();

                            if(isset($reservation)){
                                App::abort(400, 'The entity is already reserved at that time');
                            }else{
                                return Reservation::create(
                                    array(
                                        'from' => $from,
                                        'to' => $to,
                                        'subject' => Input::get('subject'),
                                        'comment' => Input::get('comment'),
                                        'announce' => json_encode(Input::get('announce')),
                                        'entity_id' => $entity->id,
                                        'user_id' => $user->id,
                                    )
                                );
                            }
                        }else{
                            App::abort(400, 'The entity is not available at that time');
                        }
                                                
                    }
                }else{
                    $this->_sendErrorMessage($reservation_validator);
                }
                                
                                
            }else{
                App::abort(403, "You are not allowed to make reservations for another user");
            }
        }else{
            App::abort(404, 'user not found');
        }
    }

    /**
     * Create a new reservation for a authenticated user.
     * @param $user_name : user's name from url.
     *
     */
    public function updateReservation($user_name, $id){

        
        $user = User::where('username', '=', $user_name)->first();
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
                                      if(!$this->isValidISO8601($value['from']))
                                          return false;
                                      if(!$this->isValidISO8601($value['to']))
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

                    $entity = Entity::where('name', '=', Input::get('entity'))
                        ->where('type', '=', Input::get('type'))
                        ->where('user_id', '=', $user->id)->first();
                                        
                    if(!isset($entity)){
                        App::abort(404, "Entity not found");
                    }else{
                        $time = Input::get('time');
                        if($this->isAvailable(json_decode($entity->body)->opening_hours, $time)){

                            //FIXME
                            $from = date("U",strtotime($time['from']));
                            $to = date("U",strtotime($time['to']));

                            $reservation = Reservation::find($id);

                            if($reservation->exists){
                                $reservation->from = $from;
                                $reservation->to = $to;
                                $reservation->subject = Input::get('subject');
                                $reservation->comment = Input::get('comment');
                                $reservation->announce = json_encode(Input::get('announce'));
                                $reservation->entity_id = $entity->id;
                                $reservation->user_id = $user->id;
                                return $reservation->save();
                            }
                                                        
                        }else{
                            App::abort(400, 'The entity is not available at that time');
                        }
                                                
                    }
                }else{
                    $this->_sendErrorMessage($reservation_validator);
                }
                                
                                
            }else{
                App::abort(403, "You are not allowed to make reservations for another user");
            }
        }else{
            App::abort(404, 'user not found');
        }
    }

    /**
     * Cancel the reservation with id $id by deleting it from database.
     * @param $user_name : the user's name
     * @param $id : the reservation's id
     */
    public function deleteReservation($user_name, $id) {
        
        $user = User::where('username', '=', $user_name)->first();

        if(isset($user)){
                
            $reservation = Reservation::find($id);

            if(isset($reservation))
                $reservation->delete();
            else
                App::abort(404, 'Reservation not found');
                        
        }else{
            App::abort(404, 'user not found');
        }
    }
}


