<?php

/**
 * This class take care of everything related to entities and amenities
 * (create / update / delete).
 */
class CustomerController extends Controller {
    public function getCustomer($user_name) {
        return Response::json(array(
                                  "things" => Config::get('app.url') . $user_name . "/things",
                                  "amenities" => Config::get('app.url') . $user_name . "/amenities",
                                  "reservations" => Config::get('app.url') . $user_name . "/reservations"
                              ));
    }
}
