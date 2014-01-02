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
     * @param opening_hours : the room's opening hours
     * @param reservation_time : the reservation's time (from & to)
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
                  && $opening_hour->dayOfWeek == date('N', $to)
              ) {
                  // check validdate after confirming the dayofweek is right
                  // valid date can differ each opening interval
                  if ($from < strtotime($opening_hour->validFrom)) {
                      return false;
                  }
                  if ($to > strtotime($opening_hour->validThrough)) {
                      return false;
                  }

                  $from_t = new DateTime();
                  $from_t->setTimestamp($from);
                  $from_open_t = clone $from_t;
                  $from_close_t = clone $from_t;
                  $to_t = new DateTime();
                  $to_t->setTimestamp($to);
                  $to_open_t = clone $to_t;
                  $to_close_t = clone $to_t;

                  foreach (array_combine($opening_hour->opens, $opening_hour->closes) 
                    as $open => $close) {
                      $i=0;

                      //we parse hour time
                      $open = explode(':', $open);
                      $open_hours = intval($open[0]);
                      $open_minutes = intval($open[1]);

                      //we parse closing time
                      $close = explode(':', $close);
                      $close_hours = intval($close[0]);
                      $close_minutes = intval($close[1]);

                      //building opening and closing time for requested day
                      $from_open_t->setTime($open_hours, $open_minutes);
                      $from_close_t->setTime($close_hours, $close_minutes);
                      $to_open_t->setTime($open_hours, $open_minutes);
                      $to_close_t->setTime($close_hours, $close_minutes);

                      if ($from_t >= $from_open_t)
                        $i++;
                      if ($from_t < $from_close_t)
                          $i--;
                      if ($to_t > $to_open_t)
                        $i++; 
                      if ($to_t <= $to_close_t)
                        $i--;
                      if (!$i) $available=true;
                  }
                  
              }
          }
          return $available;
      }


    /**
     * Return a list of reservations that the user has made for the current day.
     * Day can be change by providing a 'day' as GET parameter.
     *
     * @param clustername : the cluster's name
     * @return 
     */ 
    public function getReservations(Cluster $cluster)
    {
        /*  Announce value is json encoded in db so we first retrieve
            reservations from db, decode announce json and return
            reservations to the user */
        if (Input::get('day')!=null) {
            $from = new DateTime(Input::get('day'));
        }
        else {
            $from = new DateTime();
            $from->setTime(0,0);
        }
        $from->setTimezone(new DateTimeZone('UTC'));

        $_reservations = DB::select(
            'select * from reservation where user_id = ?
            AND UNIX_TIMESTAMP(`from`) >= ?
            AND UNIX_TIMESTAMP(`to`) <= ?',
            array($cluster->user->id, $from->getTimestamp(), $from->getTimestamp()+(60*60*24))
        );

        //FIXME : return entity name instead of id ?
        $reservations = array();
        foreach($_reservations as $reservation){
            $reservation->from = date('c', strtotime($reservation->from));
            $reservation->to = date('c', strtotime($reservation->to));
            $reservation->announce = json_decode($reservation->announce);
            $reservation->customer = json_decode($reservation->customer);
            array_push($reservations, $reservation);
        }
        return Response::json($reservations);
    }

    /**
     * Return a the reservation that has id $id.
     * @param clustername : the cluster's name
     * @param id : the id of the reservation to be deleted
     */
    public function getReservation(Cluster $cluster, $id)
    {
        //TODO the cluster is not used. So as long as a valid cluster is given every reservation in the whole api can be returned
        $reservation = Reservation::find($id);
        if(isset($reservation)) {
            $reservation->from = date('c', strtotime($reservation->from));
            $reservation->to = date('c', strtotime($reservation->to));
            $reservation->customer = json_decode($reservation->customer);
            return Response::json($reservation);
        } else {
          return $this->_sendErrorMessage(404, "Reservation.NotFound", "Reservation not found");
        }

    }

    /**
     * Return a the reservation that has id $id.
     * @param clustername : the user's name
     * @param name : the thing's name
     */
    public function getReservationsByThing(Cluster $cluster, $name)
    {
        $thing = Entity::where('user_id', '=', $cluster->user->id)->where('name', '=', $name)->first();
        if (isset($thing)) {

            if (Input::get('day')!=null) {
                $from = new DateTime(Input::get('day'));
            }
            else {
                $from = new DateTime();
                $from->setTime(0,0);
            }
            $from->setTimezone(new DateTimeZone('UTC'));

            $_reservations = DB::select(
                'select * from reservation where user_id = ?
                AND entity_id = ?
                AND UNIX_TIMESTAMP(`from`) > ?
                AND UNIX_TIMESTAMP(`to`) < ?',
                array($cluster->user->id, $thing->id, $from->getTimestamp(), $from->getTimestamp()+(60*60*24))
            );

            $reservations = array();
            foreach($_reservations as $reservation){
                $reservation->from = date('c', strtotime($reservation->from));
                $reservation->to = date('c', strtotime($reservation->to));
                $reservation->announce = json_decode($reservation->announce);
                $reservation->customer = json_decode($reservation->customer);
                array_push($reservations, $reservation);
            }
            return Response::json($reservations);
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

                        $reservation = DB::table('reservation')
                            ->where('user_id', '=', $cluster->user->id)
                            ->where('entity_id', '=', $thing->id)
                            ->where('from', '<', $to)
                            ->where('to', '>', $from)
                            ->get();

                        if(!empty($reservation)){
                            return $this->_sendErrorMessage(404, "Thing.AlreadyReserved", "The thing is already reserved at that time.");
                        }else{
                            return Reservation::create(
                                array(
                                    'from' => $from->getTimestamp(),
                                    'to' => $to->getTimestamp(),
                                    'subject' => Input::json()->get('subject'),
                                    'comment' => Input::json()->get('comment'),
                                    'announce' => json_encode(Input::json()->get('announce')),
                                    'customer' => json_encode(Input::json()->get('customer')),
                                    'entity_id' => $thing->id,
                                    'user_id' => $cluster->user->id,
                                )
                            );
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

                        $reservation = DB::table('reservation')
                          ->where('user_id', '=', $cluster->user->id)
                          ->where('entity_id', '=', $entity->id)
                          ->where('from', '<', $to)
                          ->where('to', '>', $from)
                          ->get();

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
}


