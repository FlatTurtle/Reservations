<?php

/**
 * This class take care of everything related to entities and amenities
 * (create / update / delete).
 */
class EntityController extends Controller {
   
    
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

                
                $content = Request::instance()->getContent(); 
                if (empty($content)) 
                  App::abort(400, 'Payload is null');
                if (Input::json() == null)
                  App::abort(400, "JSON payload is invalid.");
                
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
                    $messages = $room_validator->messages();
                    $s = "JSON does not validate. Violations:\n";
                    foreach ($messages->all() as $message) {
                        $s .= "$message\n";
                    }
                    App::abort(400, $s);
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

                $content = Request::instance()->getContent(); 
                if (empty($content)) 
                  App::abort(400, 'Payload is null.');
                if (Input::json() == null)
                  App::abort(400, "JSON payload is invalid.");

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
