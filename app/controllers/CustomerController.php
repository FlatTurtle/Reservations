<?php

/**
 * This class take care of everything related to entities and amenities
 * (create / update / delete).
 */
class CustomerController extends Controller {
    public function getCustomer(Cluster $cluster) {
        return Response::json(array(
                                  "things" => Config::get('app.url') . $cluster->clustername . "/things",
                                  "amenities" => Config::get('app.url') . $cluster->clustername . "/amenities",
                                  "reservations" => Config::get('app.url') . $cluster->clustername . "/reservations"
                              ));
    }
}
