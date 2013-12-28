<?php

/**
 * This class take care of everything related to entities and amenities
 * (create / update / delete).
 */
class EntityController extends BaseController {

    /**
     * Retrieve and return all entities that the user can book.
     * @param $clustername : cluster's name from url.
     *
     */
    public function getEntities($clustername) {
      
        $cluster = $this->getCluster($clustername);

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
    }

    /**
     * Retrieve and return all amenities that the user can book.
     * @param $clustername : cluster's name from url.
     *
     */
    public function getAmenities($clustername) {
        
        $cluster = $this->getCluster($clustername);

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
    }

    /**
     * Retrieve and return the amenity called $name.
     * @param $clustername : cluster's name from url.
     * @param $name : the amenity's name
     *
     */
    public function getAmenityByName($clustername, $name) {
        $cluster = $this->getCluster($clustername);
        $amenity = Entity::whereRaw(
            'user_id = ? and type = ? and name = ?',
            array($cluster->user->id, 'amenity', $name)
        )->first();

        if (!isset($amenity)) {
            return $this->_sendErrorMessage(404, "Amenity.NotFound", "Amenity not found.");
        } else {
            $d = json_decode($amenity->body);
            $d->id = $amenity->id;
            return Response::json($d);
        }
    }


    /**
     * Retrieve and return the entity called $name.
     * @param $clustername : cluster's name from url.
     * @param $name : the entity's name
     *
     */
    public function getEntityByName($clustername, $name) {

        $cluster = $this->getCluster($clustername);
        $entity = Entity::where('user_id', '=', $cluster->user->id)
            ->where('name', '=', $name)
            ->first();

        if (!isset($entity)) {
            return $this->_sendErrorMessage(404, "Thing.NotFound", "Thing not found.");
        } else {
            $d = json_decode($entity->body);
            $d->id = $entity->id;
            return Response::json($d);
        }
    }

    /**
     * Create a new entity.
     * @param $clustername : cluster's name from the url
     * @param $name : the name of the entity to be created
     *
     */
    public function createEntity($clustername, $name) {

        $cluster = $this->getCluster($clustername);

        if (!strcmp($clustername, Auth::user()->clustername) || Auth::user()->isAdmin()) {

            $content = Request::instance()->getContent();
            if (empty($content))
              return $this->_sendErrorMessage(400, "Payload.Null", "Received payload is empty.");
            if (Input::json() == null)
              return $this->_sendErrorMessage(400, "Payload.Invalid", "Received payload is invalid.");

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
                              'message' => 'Thing successfully updated.'
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
                return $this->_sendValidationErrorMessage($room_validator);
            }
        } else {
            return $this->_sendErrorMessage(403, "WriteAccessForbiden", "You can't create things on behalf of another user.");
        }
    }

    /**
     * Create a new amenity.
     * @param $clustername : cluster's name from the url
     * @param $name : the name of the amenity to be created
     *
     */
    public function createAmenity($clustername, $name) {

        $content = Request::instance()->getContent(); 
        if (empty($content)){
            return $this->_sendErrorMessage(400, "Payload.Null", "Received payload is empty.");
        }
        if (Input::json() == null){
            return $this->_sendErrorMessage(400, "Payload.Invalid", "Received payload is invalid.");
        }
        
        $cluster = $this->getCluster($clustername);
            
        if (!strcmp($clustername, Auth::user()->clustername) || Auth::user()->isAdmin()) {
            /* This Validator verify that the schema value is a valid json-schema
               definition. */
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
                return $this->_sendValidationErrorMessage($amenity_validator);
            }
        } else {
            return $this->_sendErrorMessage(403, "WriteAccessForbiden", "You can't create amenities on behalf of another user.");
        }
    }

    /**
     * Delete an amenity.
     * @param $clustername : cluster's name from the url
     * @param $name : the name of the amenity to be deleted
     *
     */
    public function deleteAmenity($clustername, $name) {

        $cluster = $this->getCluster($clustername);

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
                    return $this->_sendErrorMessage(500, "Amenity.Unknown", "An error occured while deleting the amenity.");
            else
                return $this->_sendErrorMessage(404, "Amenity.NotFound", "Amenity not found.");

        } else {
            return $this->_sendErrorMessage(403, "DeleteAccessForbiden", "You can't delete amenities from another user.");
        }
    }
}
