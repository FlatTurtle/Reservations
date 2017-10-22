<?php
/**
 *
 *
 */

/**
 * This class take care of everything related to reservations
 * (create / update / delete).
 */
class ReservationController extends BaseController
{

    /**
    * Verify if a room is available by checking its $opening_hours
    * agains the $reservation_time.
    *
    * @param opening_hours : the room's opening hours
    * @param reservation_time : the reservation's time (from & to)
    *
    * @return true if the room is open at the given $reservation_time
    */
    private function isAvailable($opening_hours, $reservation_time)
    {

        $from = strtotime($reservation_time['from']);
        $to = strtotime($reservation_time['to']);
        $available = false;
        foreach ($opening_hours as $opening_hour) {

            /*
            * We do not support reservation that goes on multiple days,
            * if a user wants to book an entity on multiple days he had to
            * do reservations for each day
            */

            //compare dayOfWeek with the day value of $from and $to
            if ($opening_hour->dayOfWeek == date('N', $from)
                && $opening_hour->dayOfWeek == date('N', $to)) {

                // check validate after confirming the dayofweek is right
                // valid date can differ each opening interval
                if ($from < strtotime($opening_hour->validFrom)) {
                  return false;
                }
                if ($to > strtotime($opening_hour->validThrough)) {
                  return false;
                }

                $from_t = new DateTime();
                $from_t->setTimestamp($from);
                $to_t = new DateTime();
                $to_t->setTimestamp($to);

                // using the date part to later on change the time
                // clone to not have a reference
                $open_t = clone $from_t;
                $close_t = clone $from_t;

                foreach (array_combine($opening_hour->opens, $opening_hour->closes)
                as $open => $close) {

                    //we parse hour time
                    $open = explode(':', $open);
                    $open_hours = intval($open[0]);
                    $open_minutes = intval($open[1]);

                    //we parse closing time
                    $close = explode(':', $close);
                    $close_hours = intval($close[0]);
                    $close_minutes = intval($close[1]);

                    //building opening and closing time for requested day
                    $open_t->setTime($open_hours, $open_minutes);
                    $close_t->setTime($close_hours, $close_minutes);

                    if ($from_t >= $open_t
                        && $from_t < $close_t
                        && $to_t > $open_t
                        && $to_t <= $close_t){

                        $available=true;
                    }
                }

            }
        }
        return $available;
    }


    /**
     * Return a list of reservations that the user has made for the current day.
     * Day can be change by providing a 'day' as GET parameter.
     * This only returns activated or blocking reservations
     *
     * @param clustername : the cluster's name
     * @return
     */
    public function getReservations(Cluster $cluster)
    {
        /*  Announce value is json encoded in db so we first retrieve
            reservations from db, decode announce json and return
            reservations to the user */
        $from = $this->getDayFromInput();
        $to = clone $from;
        // add one day to $from
        $to->add(new DateInterval('P1D'));

        $_reservations = Reservation::activatedOrBlocking()
            ->where('user_id', '=', $cluster->user->id)
            ->where('from', '>=', $from)
            ->where('to', '<=', $to)
            ->get();

        //FIXME : return entity name instead of id ?
        $reservations = array();
        foreach($_reservations as $reservation){
            $reservation->announce = json_decode($reservation->announce);
            $reservation->customer = json_decode($reservation->customer);
            array_push($reservations, $reservation->toArray());
        }
        return $reservations;
    }

    /**
     * Return a the reservation that has id $id.
     * This only returns activated or blocking reservations
     * @param clustername : the cluster's name
     * @param id : the id of the reservation to be returned
     */
    public function getReservation(Cluster $cluster, $id)
    {
        //TODO the cluster is not used. So as long as a valid cluster is given every reservation in the whole api can be returned
        $reservation = Reservation::activatedOrBlocking()->find($id);
        if(isset($reservation)) {
            $reservation->announce = json_decode($reservation->customer);
            $reservation->customer = json_decode($reservation->customer);
            return $reservation;
        } else {
          return $this->_sendErrorMessage(404, "Reservation.NotFound", "Reservation not found");
        }

    }

    /**
     * Return a the reservation that has id $id.
     * This only returns activated or blocking reservations
     *
     * @param clustername : the user's name
     * @param name : the thing's name
     */
    public function getReservationsByThing(Cluster $cluster, $name)
    {
        $thing = Entity::where('user_id', '=', $cluster->user->id)->where('name', '=', $name)->first();
        if (isset($thing)) {

            $from = $this->getDayFromInput();
            $to = clone $from;
            // add X days to $from
            $to->add(new DateInterval('P1D'));

            $_reservations = Reservation::activatedOrBlocking()
                ->where('user_id', '=', $cluster->user->id)
                ->where('entity_id', '=', $thing->id)
                ->where('from', '>=', $from)
                ->where('to', '<=', $to)
                ->get();

            $reservations = array();
            foreach($_reservations as $reservation){
                $reservation->announce = json_decode($reservation->announce);
                $reservation->customer = json_decode($reservation->customer);
                array_push($reservations, $reservation->toArray());
            }
            return $reservations;
        } else{
            return $this->_sendErrorMessage(404, "Thing.NotFound", "Thing not found");
        }
    }

    /** 
     * Return the reservations for the next X days.
     */
    public function getReservationsPerXDays (Cluster $cluster, $name, $nb_days) 
    {
        if (!is_numeric($nb_days)) {
            return $this->_sendErrorMessage(400, "Payload.Invalid", "NB_days is not an integer");
        }

        $thing = Entity::where('user_id', '=', $cluster->user->id)->where('name', '=', $name)->first();
        if (isset($thing)) {
            $nb_days_as_int = intval($nb_days);
            $from = $this->getDayFromInput();
            $to = clone $from;
            // add X days to $from
            $to->add(new DateInterval('P' + $nb_days_as_int + 'D'));

            $_reservations = Reservation::activatedOrBlocking()
                ->where('user_id', '=', $cluster->user->id)
                ->where('entity_id', '=', $thing->id)
                ->where('from', '>=', $from)
                ->where('to', '<=', $to)
                ->get();

            $reservations = array();
            foreach($_reservations as $reservation){
                $reservation->announce = json_decode($reservation->announce);
                $reservation->customer = json_decode($reservation->customer);
                array_push($reservations, $reservation->toArray());
            }
            return $reservations;
        } else{
            return $this->_sendErrorMessage(404, "Thing.NotFound", "Thing not found");
        }
    }

    /**
     * Create a new reservation for a authenticated user.
     * @param $clustername : cluster's name from url.
     *
     */
    public function createReservation(Cluster $cluster){

        $content = Request::instance()->getContent();
        if (empty($content))
          return $this->_sendErrorMessage(400, "Payload.Null", "Received payload is empty.");
        if (Input::json() == null)
          return $this->_sendErrorMessage(400, "Payload.Invalid", "Received payload is invalid.");


        if(!strcmp($cluster->clustername, Auth::user()->clustername) || Auth::user()->isAdmin()){

            $thing_uri = Input::json()->get('thing');
            $thing_name = explode('/', $thing_uri);
            $thing_name = $thing_name[count($thing_name)-1];
            $thing_uri = str_replace($thing_name, '', $thing_uri);
            Input::json()->set('thing', $thing_uri);

            $reservation_validator = Validator::make(
                Input::json()->all(),
                array(
                    'thing' => 'required|url',
                    'type' => 'required',
                    'time' => 'required|time',
                    'subject' => 'required',
                    'announce' => 'required',
                    'customer' => 'required|customer'
                )
            );


            if(!$reservation_validator->fails()){

                $thing = Entity::where('name', '=', $thing_name)
                    ->where('type', '=', Input::json()->get('type'))
                    ->where('user_id', '=', $cluster->user->id)->first();

                if(!isset($thing)){
                    return $this->_sendErrorMessage(404, "Thing.NotFound", "Thing not found.");
                }else{
                    $time = Input::json()->get('time');
                    if($this->isAvailable(json_decode($thing->body)->opening_hours, $time)){

                        //timestamps are UTC so we convert dates to UTC timezone

                        $from = new DateTime($time['from']);
                        $to = new DateTime($time['to']);
                        $from->setTimezone(new DateTimeZone('UTC'));
                        $to->setTimezone(new DateTimeZone('UTC'));

                        $reservation = Reservation::activatedOrBlocking()
                            ->where('user_id', '=', $cluster->user->id)
                            ->where('entity_id', '=', $thing->id)
                            ->where('from', '<', $to)
                            ->where('to', '>', $from)
                            ->first();

                        if(!empty($reservation)){
                            return $this->_sendErrorMessage(404, "Thing.AlreadyReserved", "The thing is already reserved at that time.");
                        }else{

                            // Generate an activation code
                            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            $code = substr(str_shuffle(str_repeat($pool, 5)), 0, 16);

                            // All systems go, create reservation
                            $reservation = Reservation::create(
                                array(
                                    'from' => $from->getTimestamp(),
                                    'to' => $to->getTimestamp(),
                                    'subject' => Input::json()->get('subject'),
                                    'comment' => Input::json()->get('comment'),
                                    'announce' => json_encode(Input::json()->get('announce')),
                                    'customer' => json_encode(Input::json()->get('customer')),
                                    'entity_id' => $thing->id,
                                    'user_id' => $cluster->user->id,
                                    'activated' => false,
                                    'code' => $code,
                                )
                            );

                            // Get customer
                            $customer = Input::json()->get('customer');

                            // Email data
                            $from = new DateTime($reservation->from);
                            $to   = new DateTime($reservation->to);
                            
                            $data = array(
                                'thing_name'  => $thing_name,
                                'from'        => $from->format('d-m-Y H:i'),
                                'to'          => $to->format('d-m-Y H:i'),
                                'reservation' => $reservation,
                                'customer'    => $customer,
                                'confirm_url' => $cluster->clustername . '/reservations/confirm/' . $code,
                                'cancel_url'  => $cluster->clustername . '/reservations/cancel/' . $code,
                            );

                            // Send confirmation email
                            Mail::send('emails.confirm', $data, function($message) use ($customer)
                            {
                                $message->to($customer['email'], $customer['email'])->subject("Confirm your reservation.");
                            });

                            // Send object back to API
                            return $reservation;
                        }
                    }else{
                      return $this->_sendErrorMessage(404, "Thing.Unavailable", "The thing is unavailable at that time.");
                    }
                }
            }else{
                return $this->_sendValidationErrorMessage($reservation_validator);
            }
        }else{
            return $this->_sendErrorMessage(403, "WriteAccessForbiden", "You can't make reservations on behalf of another user.");
        }
    }

    /**
     * Create a new reservation for a authenticated user.
     * @param $clustername : cluster's name from url.
     *
     */
    public function updateReservation(Cluster $cluster, $id){

        if(!strcmp($cluster->clustername, Auth::user()->clustername) || Auth::user()->isAdmin()){

            $content = Request::instance()->getContent();
            if (empty($content))
              return $this->_sendErrorMessage(400, "Payload.Null", "Received payload is empty.");
            if (Input::json() == null)
              return $this->_sendErrorMessage(400, "Payload.Invalid", "Received payload is invalid.");

            $thing_uri = Input::json()->get('thing');
            $thing_name = explode('/', $thing_uri);
            $thing_name = $thing_name[count($thing_name)-1];
            $thing_uri = str_replace($thing_name, '', $thing_uri);
            Input::json()->set('thing', $thing_uri);

            $reservation_validator = Validator::make(
                Input::json()->all(),
                array(
                    'thing' => 'required|url',
                    'type' => 'required',
                    'time' => 'required|time',
                    'subject' => 'required',
                    'announce' => 'required',
                    'customer' => 'required|customer'
                )
            );

            if(!$reservation_validator->fails()){

                $entity_name = explode('/', Input::json()->get('thing'));
                $entity_name = $entity_name[count($entity_name)-1];

                $entity = Entity::where('name', '=', $entity_name)
                    ->where('type', '=', Input::json()->get('type'))
                    ->where('user_id', '=', $cluster->user->id)->first();

                if(!isset($entity)){
                    return $this->_sendErrorMessage(404, "Thing.NotFound", "Thing not found.");
                }else{
                    $reservation = Reservation::find($id);
                    if($reservation->exists){
                      $time = Input::json()->get('time');
                      if($this->isAvailable(json_decode($entity->body)->opening_hours, $time)){
                        //timestamps are UTC so we convert dates to UTC timezone
                        $from = new DateTime($time['from']);
                        $to = new DateTime($time['to']);
                        $from->setTimezone(new DateTimeZone('UTC'));
                        $to->setTimezone(new DateTimeZone('UTC'));

                          $reservation = Reservation::activatedOrBlocking()
                              ->where('user_id', '=', $cluster->user->id)
                              ->where('entity_id', '=', $entity->id)
                              ->where('from', '<', $to)
                              ->where('to', '>', $from)
                              ->first();

                        if(!empty($reservation)){
                            return $this->_sendErrorMessage(404, "Thing.AlreadyReserved", "The thing is already reserved at that time.");
                        }else{
                              $reservation->from = $from->getTimestamp();
                              $reservation->to = $to->getTimestamp();
                              $reservation->subject = Input::json()->get('subject');
                              $reservation->comment = Input::json()->get('comment');
                              $reservation->announce = json_encode(Input::json()->get('announce'));
                              $reservation->customer = json_encode(Input::json()->get('customer'));
                              $reservation->entity_id = $entity->id;
                              $reservation->user_id = $cluster->user->id;
                              return $reservation->save();
                        }

                    }else{
                        return $this->_sendErrorMessage(404, "Thing.Unavailable", "The thing is unavailable at that time.");
                    }
                  }
                }
            }else{
                return $this->_sendValidationErrorMessage($reservation_validator);
            }


        }else{
            return $this->_sendErrorMessage(403, "WriteAccessForbiden", "You can't make reservations on behalf of another user.");
        }
    }

    /**
     * Cancel the reservation with id $id by deleting it from database.
     * @param $clustername : the cluster's name
     * @param $id : the reservation's id
     */
    public function deleteReservation(Cluster $cluster, $id) {
        //TODO cluster not used with a valid cluster anyone can delete any reservation
        $reservation = Reservation::find($id);

        if(isset($reservation)){
            $reservation->delete();
        }else{
            return $this->_sendErrorMessage(404, "Reservation.NotFound", "Reservation not found.");
        }
    }

    /**
     * Confirm a reservation by code.
     * @param  Cluster $cluster
     * @param  string  $code
     */
    public function getConfirm(Cluster $cluster, $code)
    {
        // Get the reservation object
        $reservation = Reservation::where('code', $code)->first();
        if($reservation == null)
            return View::make('invalid');

        // Activate the reservation
        $reservation->activated = true;
        $reservation->save();

        // Show some HTML
        return View::make('confirmed');
    }

    /**
     * Cancel a reservation by code.
     * @param  Cluster $cluster
     * @param  string  $code
     */
    public function getCancel(Cluster $cluster, $code)
    {
        // Get the reservation object
        $reservation = Reservation::where('code', $code)->first();
        if($reservation == null)if($reservation == null)
            return View::make('cancelled');

        // Delete the reservation
        Reservation::where('code', $code)->delete();

        // Show some HTML
        return View::make('cancelled');
    }

    private function getDayFromInput()
    {
        if (Input::get('day') != null) {
            $from = new DateTime(Input::get('day'));
        } else {
            $from = new DateTime();
            $from->setTime(0, 0);
        }
        $from->setTimezone(new DateTimeZone('UTC'));
        return $from;
    }
}


