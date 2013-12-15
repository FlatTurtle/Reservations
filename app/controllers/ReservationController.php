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
     * @param clustername : the cluster's name
     * @return 
     */ 
    public function getReservations($clustername)
    {
        $cluster = Cluster::where('clustername', '=', $clustername)->first();

        if (isset($cluster)) {
            /*  Announce value is json encoded in db so we first retrieve 
                reservations from db, decode announce json and return 
                reservations to the user */
            if (Input::get('day')!=null)
                $from = strtotime(Input::get('day'));
            else
                $from = mktime(0,0,0);

            $to = $from+(60*60*24);
            $_reservations = DB::select(
                  'select * from reservation where user_id = ? 
                  AND UNIX_TIMESTAMP(`from`) >= ? 
                  AND UNIX_TIMESTAMP(`to`) <= ?', 
                  array($cluster->user->id, $from, $to));

            //FIXME : return entity name instead of id ?
            $reservations = array();
            foreach($_reservations as $reservation){
                $reservation->from = date('c', strtotime($reservation->from));
                $reservation->to = date('c', strtotime($reservation->to));
                $reservation->announce = json_decode($reservation->announce);
                array_push($reservations, $reservation);
            }
            return Response::json($reservations);

        }else{
          App::abort(404, 'cluster not found');
        }
    }

    /**
     * Return a the reservation that has id $id.
     * @param clustername : the cluster's name
     * @param id : the id of the reservation to be deleted
     */
    public function getReservation($clustername, $id)
    {
        $cluster = Cluster::where('clustername', '=', $clustername)->first();
        if (isset($cluster)) {
            $reservation = Reservation::find($id);
            if(isset($reservation)) {
                $reservation->from = date('c', strtotime($reservation->from));
                $reservation->to = date('c', strtotime($reservation->to));
                return Response::json($reservation);
            } else {
                App::abort(404, 'Reservation not found.');
            }
        } else {
            App::abort(404, 'cluster not found');
        }
    }

    /**
     * Return a the reservation that has id $id.
     * @param clustername : the user's name
     * @param name : the thing's name
     */
    public function getReservationsByThing($clustername, $name)
    {
        $cluster = Cluster::where('clustername', '=', $clustername)->first();
        if (isset($cluster)) {
            $thing = Entity::where('user_id', '=', $cluster->user->id)->where('name', '=', $name)->first();
            if (isset($thing)) {

              if (Input::get('day')!=null)
                $from = strtotime(Input::get('day'));
              else
                  $from = mktime(0,0,0);
              $to = $from+(60*60*24);

              $_reservations = DB::select(
                  'select * from reservation where user_id = ?
                  AND entity_id = ? 
                  AND UNIX_TIMESTAMP(`from`) > ? 
                  AND UNIX_TIMESTAMP(`to`) < ?', 
                  array($cluster->user->id, $thing->id, $from, $to));
              $reservations = array();
              foreach($_reservations as $reservation){
                $reservation->from = date('c', strtotime($reservation->from));
                $reservation->to = date('c', strtotime($reservation->to));
                $reservation->announce = json_decode($reservation->announce);
                array_push($reservations, $reservation);
              }
              return Response::json($reservations);
            } else{
              App::abort(404, 'Thing not found');
            }
        } else {
            App::abort(404, 'user not found');
        }
    }

        

    

    /**
     * Create a new reservation for a authenticated user.
     * @param $clustername : cluster's name from url.
     *
     */
    public function createReservation($clustername){

        $cluster = Cluster::where('clustername', '=', $clustername)->first();
        if(isset($cluster)){

            if(!strcmp($clustername, Auth::user()->clustername) || Auth::user()->isAdmin()){
                
                $content = Request::instance()->getContent(); 
                if (empty($content)) 
                  App::abort(400, 'Payload is null.');
                if (Input::json() == null)
                  App::abort(400, "JSON payload is invalid.");

                $reservation_validator = Validator::make(
                    Input::json()->all(),
                    array(
                        'thing' => 'required|url',
                        'type' => 'required',
                        'time' => 'required|time',
                        'comment' => 'required',
                        'subject' => 'required',
                        'announce' => 'required'
                    )
                );


                if(!$reservation_validator->fails()){

                    $entity_name = explode('/', Input::json()->get('thing'));
                    $entity_name = $entity_name[count($entity_name)-1];
                    $thing = Entity::where('name', '=', $entity_name)
                        ->where('type', '=', Input::json()->get('type'))
                        ->where('user_id', '=', $cluster->user->id)->first();
                                        
                    if(!isset($thing)){
                        App::abort(404, "Entity not found");
                    }else{
                        $time = Input::json()->get('time');
                        if($this->isAvailable(json_decode($thing->body)->opening_hours, $time)){

                
                            $from = strtotime($time['from']);
                            $to = strtotime($time['to']);


                            $reservation = DB::select(
                              'select * from reservation where user_id = ?
                              AND entity_id = ? 
                              AND `from` > ? 
                              AND `to` < ?', 
                              array($cluster->user->id, $thing->id, $from, $to));

                            print_r($reservation);
                            if(!empty($reservation)){
                                App::abort(400, 'The thing is already reserved at that time');
                            }else{
                                return Reservation::create(
                                    array(
                                        'from' => $from,
                                        'to' => $to,
                                        'subject' => Input::json()->get('subject'),
                                        'comment' => Input::json()->get('comment'),
                                        'announce' => json_encode(Input::json()->get('announce')),
                                        'entity_id' => $thing->id,
                                        'user_id' => $cluster->user->id,
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
            App::abort(404, 'cluster not found');
        }
    }

    /**
     * Create a new reservation for a authenticated user.
     * @param $clustername : cluster's name from url.
     *
     */
    public function updateReservation($clustername, $id){

        
        $cluster = Cluster::where('clustername', '=', $clustername)->first();
        if(isset($cluster)){
                        
            if(!strcmp($clustername, Auth::user()->clustername) || Auth::user()->isAdmin()){
                
                $content = Request::instance()->getContent(); 
                if (empty($content)) 
                  App::abort(400, 'Payload is null.');
                if (Input::json() == null)
                  App::abort(400, "JSON payload is invalid.");

                $reservation_validator = Validator::make(
                    Input::json()->all(),
                    array(
                        'thing' => 'required|url',
                        'type' => 'required',
                        'time' => 'required|time',
                        'comment' => 'required',
                        'subject' => 'required',
                        'announce' => 'required'
                    )
                );

                if(!$reservation_validator->fails()){

                    $entity_name = explode('/', Input::json()->get('thing'));
                    $entity_name = $entity_name[count($entity_name)-1];

                    $entity = Entity::where('name', '=', $entity_name)
                        ->where('type', '=', Input::json()->get('type'))
                        ->where('user_id', '=', $cluster->user->id)->first();
                                        
                    if(!isset($entity)){
                        App::abort(404, "Entity not found");
                    }else{
                        $time = Input::json()->get('time');
                        if($this->isAvailable(json_decode($entity->body)->opening_hours, $time)){

                            $from = strtotime($time['from']);
                            $to = strtotime($time['to']);

                            $reservation = Reservation::find($id);

                            if($reservation->exists){
                                $reservation->from = $from;
                                $reservation->to = $to;
                                $reservation->subject = Input::json()->get('subject');
                                $reservation->comment = Input::json()->get('comment');
                                $reservation->announce = json_encode(Input::json()->get('announce'));
                                $reservation->entity_id = $entity->id;
                                $reservation->user_id = $cluster->user->id;
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
            App::abort(404, 'cluster not found');
        }
    }

    /**
     * Cancel the reservation with id $id by deleting it from database.
     * @param $clustername : the cluster's name
     * @param $id : the reservation's id
     */
    public function deleteReservation($clustername, $id) {
        
        $cluster = Cluster::where('clustername', '=', $clustername)->first();

        if(isset($cluster)){
                
            $reservation = Reservation::find($id);

            if(isset($reservation))
                $reservation->delete();
            else
                App::abort(404, 'Reservation not found');
                        
        }else{
            App::abort(404, 'cluster not found');
        }
    }
}


