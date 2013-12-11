<?php

/**
 * This class take care of everything related to entities and amenities
 * (create / update / delete).
 */
class EntityController extends Controller {
   
    /**
     * This function throw an error 400 and provide error messages
     * from validator $validator in JSON.
     * @param $validator : a Laravel validator
     */
    private function sendErrorMessage($validator){

        //FIXME : define a schema to return errors as json
        $messages = $validator->messages();
        $s = "JSON does not validate. Violations:\n";
        foreach ($messages->all() as $message) {
            $s .= "$message\n";
        }
        App::abort(400, $s);
    }

    /**
     * Retrieve and return all entities that the user can book.
     * @param $clustername : cluster's name from url.
     *
     */
    public function getEntities($clustername) {
      
        $cluster = Cluster::where('clustername', '=', $clustername)->first();

        if (isset($cluster)) {

            /* retrieve all entities from db and push their json bodies into an array
               that we return to the user as json */

            $_entities = Entity::where('user_id', '=', $cluster->user->id)->get()->toArray();
            $entities = array();
            $i = 0;
            foreach ($_entities as $entity) {
                if (isset($entity['body'])) {
                    $entities[$i] = json_decode($entity['body']);
                    if (is_null($entities[$i])) {
                        $entities[$i] = new stdClass();
                    }
                    $entities[$i]->id = $entity['id'];
                    $i++;
                }
            }
            return Response::json($entities);
                        
        } else {
            App::abort(404, 'user not found');
        }
    }

    /**
     * Retrieve and return all amenities that the user can book.
     * @param $clustername : cluster's name from url.
     *
     */
    public function getAmenities($clustername) {
        
        $cluster = Cluster::where('clustername', '=', $clustername)->first();

        if (isset($cluster)) {

            /* retrieve all entities with type 'amenity' 
               from db and push their json bodies into an array
               that we return to the user as json */
            $amenities = Entity::where('user_id', '=', $cluster->user->id)
            ->where('type', '=', 'amenity')
            ->get()
            ->toArray();

            foreach ($amenities as $amenity) {
                if (isset($amenity['body'])) {
                    $amenity['body'] = json_decode($amenity['body']);        
                } 
            }
            return Response::json($amenities);
            
        } else {
            App::abort(404, 'user not found');
        }
    }

    /**
     * Retrieve and return the amenity called $name.
     * @param $clustername : cluster's name from url.
     * @param $name : the amenity's name
     *
     */
    public function getAmenityByName($clustername, $name) {

    
        $cluster = Cluster::where('clustername', '=', $clustername)->first();

        if (isset($cluster)) {
            $amenity = Entity::whereRaw(
                'user_id = ? and type = ? and name = ?',
                array($cluster->user->id, 'amenity', $name)
            )->first();

            if (!isset($amenity)) {
                App::abort(404, 'Amenity not found');
            } else {
                $d = json_decode($amenity->body);
                $d->id = $amenity->id;
                return Response::json($d);
            }
            
        } else {
            App::abort(404, 'user not found');
        }
    }


    /**
     * Retrieve and return the entity called $name.
     * @param $clustername : cluster's name from url.
     * @param $name : the entity's name
     *
     */
    public function getEntityByName($clustername, $name) {

        $cluster = Cluster::where('clustername', '=', $clustername)->first();

        if (isset($cluster)) {
            $entity 
                = Entity::where('user_id', '=', $cluster->user->id)
                ->where('name', '=', $name)
                ->first();
            if (!isset($entity)) {
                App::abort(404, 'Entity not found');
            } else {
                $d = json_decode($entity->body);
                $d->id = $entity->id;
                return Response::json($d);
            }
            
        } else {
            App::abort(404, 'user not found');
        }
    }

    /**
     * Create a new entity.
     * @param $clustername : cluster's name from the url
     * @param $name : the name of the entity to be created
     *
     */
    public function createEntity($clustername, $name) {

        $cluster = Cluster::where('clustername', '=', $clustername)->first();
        if (isset($cluster)) {

            if (!strcmp($clustername, Auth::user()->clustername) || Auth::user()->isAdmin()) {

                Validator::extend('hours',
                  function($attribute, $value, $parameters)
                  {
                    foreach($value as $hour) {
                      //validate 24 hours format time
                      if(!preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/", $hour))
                        return false;
                    }
                    return true;
                  }
                );
                Validator::extend(
                    'opening_hours', 
                    function ($attribute, $value, $parameters)
                    {
                        $now = date("Y-m-d h:m:s", time());
                        foreach ($value as $opening_hour) {
                            $opening_hour_validator = Validator::make(
                                $opening_hour,
                                array(
                                    'opens' => 'required|hours',
                                    'closes' => 'required|hours',
                                    'validFrom' => 'required',
                                    'validThrough' => 'required|after:'.$now,
                                    'dayOfWeek' => 'required|numeric|between:0,7'
                                )
                            );
                            if ($opening_hour_validator->fails())
                                $this->sendErrorMessage($opening_hour_validator);
                        }
                        return true;
                    }
                );


                Validator::extend(
                    'price',
                    function ($attribute, $value, $parameters)
                    {
                        /* we verify that the price object has at least 
                          one defined time rate and that the currency is one of the
                          ISO4217 standard */

                        $timings = array(
                          'hourly',
                          'daily',
                          'weekly',
                          'monthly',
                          'yearly'
                        );
                        $ISO4217 = array(
                          "AED","AFN","ALL","AMD","ANG","AOA","ARS","AUD","AWG",
                          "AZN","BAM","BBD","BDT","BGN","BHD","BIF","BMD","BND",
                          "BOB","BOV","BRL","BSD","BTN","BWP","BYR","BZD","CAD",
                          "CDF","CHE","CHF","CHW","CLF","CLP","CNY","COP","COU",
                          "CRC","CUC","CUP","CVE","CZK","DJF","DKK","DOP","DZD",
                          "EGP","ERN","ETB","EUR","FJD","FKP","GBP","GEL","GHS",
                          "GIP","GMD","GNF","GTQ","GYD","HKD","HNL","HRK","HTG",
                          "HUF","IDR","ILS","INR","IQD","IRR","ISK","JMD","JOD",
                          "JPY","KES","KGS","KHR","KMF","KPW","KRW","KWD","KYD",
                          "KZT","LAK","LBP","LKR","LRD","LSL","LTL","LVL","LYD",
                          "MAD","MDL","MGA","MKD","MMK","MNT","MOP","MRO","MUR",
                          "MVR","MWK","MXN","MXV","MYR","MZN","NAD","NGN","NIO",
                          "NOK","NPR","NZD","OMR","PAB","PEN","PGK","PHP","PKR",
                          "PLN","PYG","QAR","RON","RSD","RUB","RWF","SAR","SBD",
                          "SCR","SDG","SEK","SGD","SHP","SLL","SOS","SRD","SSP",
                          "STD","SVC","SYP","SZL","THB","TJS","TMT","TND","TOP",
                          "TRY","TTD","TWD","TZS","UAH","UGX","USD","USN","USS",
                          "UYI","UYU","UZS","VEF","VND","VUV","WST","XAF","XAG",
                          "XAU","XBA","XBB","XBC","XBD","XCD","XDR","XFU","XOF",
                          "XPD","XPF","XPT","XSU","XTS","XUA","XXX","YER","ZAR",
                          "ZMW","ZWL"
                        );

                        $intersect = array_intersect($timings, array_keys($value));
                        foreach ($intersect as $index) {
                            if ($value[$index] < 0)
                                return false;
                        }
                        return (
                          isset($value['currency']) && 
                          in_array($value['currency'], $ISO4217) && 
                          !empty($intersect)
                        );
                    }
                );

                Validator::extend(
                    'map',
                    function ($attribute, $value, $parameters)
                    {
                        $map_validator = Validator::make(
                            $value,
                            array(
                                'img' => 'required|url',
                                'reference' => 'required'
                            )
                        );
                        if ($map_validator->fails())
                            $this->sendErrorMessage($map_validator);
                        return true;
                    }
                );
                Validator::extend(
                    'location', 
                    function ($attribute, $value, $parameters)
                    {
                        $location_validator = Validator::make(
                            $value,
                            array(
                                'map' => 'required|map',
                                'floor' => 'required|numeric',
                                'building_name' => 'required'
                            )
                        );
                        if ($location_validator->fails())
                            $this->sendErrorMessage($location_validator);
                        return true;
                    }
                );

                Validator::extend(
                    'amenities', 
                    function ($attribute, $value, $parameters)
                    {
                        $present = true;
                        if (count($value)) {
                            /* we check if amenities provided as input
                             exists in database */
                            $amenities 
                                = DB::table('entity')
                                ->where('type', '=', 'amenity')
                                ->lists('name');
                            foreach ($value as $amenity) {
                                $present = in_array($amenity, $amenities);
                            }
                        }
                        return $present;
                    }
                );

                Validator::extend(
                    'body',
                    function ($attribute, $value, $parameters)
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
                    }
                );
                $room_validator = Validator::make(
                    Input::json()->all(),
                    array(
                        'type' => 'required|alpha_dash',
                        'body' => 'required|body'
                    )
                );

                // Validator testing
                if (!$room_validator->fails()) {
                    $body = Input::json()->get('body');
                    $entity = Entity::where('name', '=', $body['name'])
                        ->where('user_id', '=', $cluster->user->id)
                        ->first();

                    if (isset($entity)) {
                        // the entity already exist in db, we update the json body.
                        $entity->body = json_encode($body);
                        if($entity->save())
                            return Response::json(
                                array(
                                  'success' => true,
                                  'message' => 'Entity successfully updated'
                                )
                            );
                    } else {
                        // the entity don't exist in db so we insert it.
                        return Entity::create(
                            array(
                                'name' => $body['name'],
                                'type' => Input::json()->get('type'),
                                'body' => json_encode($body),
                                'user_id' => $cluster->user->id
                            )
                        );
                    }
                } else {
                    $this->sendErrorMessage($room_validator);
                } 
                
            } else {
                App::abort(403, "You can't modify entities from another user");
            }
        } else {
            App::abort(404, 'user not found');
        }   
    }


    private function _validateProperty($property) {

        $property_validator
            = Validator::make(
                $property,
                array(
                  'description' => 'required',
                  'type' => 'required|schema_type'
                )
            );
        if($property_validator->fails())
            $this->sendErrorMessage($property_validator);
        if(isset($property['properties']))
            $this->_validateProperty($property['properties']);
    }
    /**
     * Create a new amenity.
     * @param $clustername : cluster's name from the url
     * @param $name : the name of the amenity to be created
     *
     */
    public function createAmenity($clustername, $name) {

        Validator::extend(
            'schema_type',
            function ($attribute, $value, $parameters)
            {
                $supported_types = array('array', 'boolean', 'integer', 'number', 
                                         'null', 'object', 'string');
                return in_array($value, $supported_types);
            }
        );

        
        $cluster = Cluster::where('clustername', '=', $clustername)->first();
        if (isset($cluster)) {
            
            if (!strcmp($clustername, Auth::user()->clustername) || Auth::user()->isAdmin()) {
                /* This Validator verify that the schema value is a valid json-schema
                   definition. */
                Validator::extend(
                    'schema',
                    function ($attribute, $value, $parameters)
                    {
                        $json = json_encode($value);
                        if ($json != null) {
          
                            $schema_validator = Validator::make(
                                $value,
                                array(
                                  '$schema' => 'required|url',
                                  'title' => 'required',
                                  'description' => 'required',
                                  'type' => 'required|schema_type',
                                  'properties' => 'required',
                                  'required' => 'required'
                                )
                            );
                            if (!$schema_validator->fails()) {
              
                                foreach ($value['required'] as $required) {
                                    if(!isset($value['properties'][$required]))
                                        return false;
                                }
                                foreach ($value['properties'] as $property) {
                                    $this->_validateProperty($property);
                                }


                            } else {
                                $this->sendErrorMessage($schema_validator);
                            }
                        } else {
                            return false;
                        }
                        return true;
                    }
                );

                $amenity_validator = Validator::make(
                    Input::json()->all(),
                    array(
                        'description' => 'required',
                        'schema' => 'required|schema'
                    )
                );


                if (!$amenity_validator->fails()) {
                    $amenity = Entity::where('name', '=', $name)->first();
                    if (isset($amenity)) {
                        $amenity->body = json_encode(Input::json()->get('schema'));
                        $amenity->save();
                    } else {
                        return Entity::create(
                            array(
                                'name' => $name,
                                'type' => 'amenity',
                                'body' => json_encode(Input::json()->get('schema')),
                                'user_id' => $cluster->user->id
                            )
                        );
                    }
                } else {
                    $this->sendErrorMessage($amenity_validator);
                }
            } else {
                App::abort(
                    403, 
                    "You are not allowed to modify amenities from another user"
                );
            }
        } else {
            App::abort(404, 'User not found');
        }               
    }

    /**
     * Delete an amenity.
     * @param $clustername : cluster's name from the url
     * @param $name : the name of the amenity to be deleted
     *
     */
    public function deleteAmenity($clustername, $name) {
        
        
        $cluster = Cluster::where('clustername', '=', $clustername)->first();
        
        if (isset($cluster)) {

            if (!strcmp($clustername, Auth::user()->clustername) || Auth::user()->isAdmin()) {
                
                $amenity = Entity::where('user_id', '=', $cluster->user->id)
                    ->where('type', '=', 'amenity')
                    ->where('name', '=', $name);

                if ($amenity->first() != null)
                    if($amenity->delete())
                        return Response::json(
                            array(
                              'success' => true,
                              'message' => 'Amenity successfully deleted'
                            )
                        );
                    else
                        App::abort(500, 'An error occured while deleting amenity.');
                else
                    App::abort(404, 'Amenity not found');
                    
            } else {
                App::abort(
                    403, 
                    "You're not allowed to delete amenities from another user"
                );
            }
            
        } else {
            App::abort(404, 'user not found');
        }
    }
}
