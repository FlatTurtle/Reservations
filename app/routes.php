<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
*/

App::error(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    return Response::json(
        array(
            'error' => $e->getMessage(),
            ),
        $e-> getStatusCode()
    );
});

/*
 * Model bindings
 *
 */
Route::bind('cluster', function($cluster_name, $route)
{
    $cluster = $cluster = Cluster::where('clustername', '=', $cluster_name)->first();
    if (!isset($cluster)) {
        throw new EntityNotFoundException("Cluster.NotFound");
    }
    return $cluster;
});

/**
 * AUTH routes
 */
Route::group(array('before' => 'auth.basic'), function()
{
    /*
      PUT http://reservation.hostname/{cluster_name}/amenities/{name}
      create or update an amenity.
    */
    Route::put(
        '/{cluster}/amenities/{name}','EntityController@createAmenity'
    );

    /*
      DELETE http://reservation.hostname/{cluster_name}/amenities/{name}
      delete a certain amenity
    */
    Route::delete(
        '/{cluster}/amenities/{name}',
        array('uses' => 'EntityController@deleteAmenity')
    );

    /*
      POST http://reservation.hostname/{cluster_name}/reservations
      create a new reservation.
    */
    Route::post(
        '/{cluster}/reservations',
        array('uses' => 'ReservationController@createReservation')
    );

    /*
      POST http://reservation.hostname/{cluster_name}/reservations/{id}
      update the reservation with
      id {id}.
    */
    Route::post(
        '/{cluster}/reservations/{id}',
        array('uses' => 'ReservationController@updateReservation')
    );

    /*
       DELETE http://reservation.hostname/{cluster_name}/reservations/{id}
       cancel the reservation {id}
    */
    Route::delete(
        '/{cluster}/reservations/{id}',
        array('uses' => 'ReservationController@deleteReservation')
    );

    /*
      PUT http://reservation.hostname/{cluster_name}/things/{name}
      create or update thing that can be reserved.
    */
    Route::put(
        '/{cluster}/things/{name}',
        array('uses' => 'EntityController@createEntity')
    );

    /*
     *  PUT http://reservation.hostname/{cluster_name}/companies/
     *  creates a new company
     */
    Route::put(
        '/{cluster}/companies/{name}',
        array('uses' => 'CompanyController@createCompany')
    );

    /*
     * DELETE http://reservation.hostname/{cluster_name}/companies/{company_name}
     * deletes the given company
     */
    Route::delete(
        '/{cluster}/companies/{name}',
        array('uses' => 'CompanyController@deleteCompany')
    );
});





/*
  Root url, this is where the API documentation will be display.
*/
Route::get(
    '/',
    array('uses' => 'HomeController@showWelcome')
);

/*
  GET http://reservation.hostname/{cluster_name}/amenities
  returns list of amenities/
*/
Route::get(
    '/{cluster}/amenities',
    array('uses' => 'EntityController@getAmenities')
);

/*
  GET http://reservation.hostname/{cluster_name}/amenities/{name}
  returns information about a
  certain amenity.
*/
Route::get(
    '/{cluster}/amenities/{name}',
    array('uses' => 'EntityController@getAmenityByName')
);

/*
  GET http://reservation.hostname/{cluster_name}/reservations
  returns list of reservations made for the current day.
  Day can be changed with the GET parameter ?day=2013-10-12
*/
Route::get(
    '/{cluster}/reservations',
    array('uses' => 'ReservationController@getReservations')
);

/*
  GET http://reservation.hostname/{cluster_name}/reservations/{id}
  return the reservation with
  id {id}.
*/
Route::get(
    '/{cluster}/reservations/{id}',
    array('uses' => 'ReservationController@getReservation')
);

/*
  GET http://reservation.hostname/{cluster_name}/reservations/confirm/{code}
  confirm the reservation
  id {id}.
*/
Route::get(
    '/{cluster}/reservations/confirm/{code}',
    array('uses' => 'ReservationController@getConfirm')
);

/*
  GET http://reservation.hostname/{cluster_name}/things
  returns informations about the things
*/
Route::get(
    '/{cluster}/things',
    array('uses' => 'EntityController@getEntities')
);


/*
  GET http://reservation.hostname/{cluster_name}/things/{name}
  returns informations about a certain thing that can be reserved.
*/
Route::get(
    '/{cluster}/things/{name}',
    array('uses' => 'EntityController@getEntityByName')
);

/*
  GET http://reservation.hostname/{cluster_name}/things/{name}/reservations
  returns reservations made on the thing {name} for the current day.
  Day can be changed with the GET parameter ?day=2013-10-12
*/
Route::get(
    '/{cluster}/things/{name}/reservations',
    array('uses' => 'ReservationController@getReservationsByThing')
);

/*
  GET http://reservation.hostname/{cluster_name}/
  returns 3 URIs thing that can be reserved.
*/
Route::get(
    '/{cluster}',
    array('uses' => 'CustomerController@getCustomer')
);


/*
 *  GET http://reservation.hostname/{cluster_name}/companies/
 *  returns all the companies registered for that cluster
 */
Route::get(
    '/{cluster}/companies',
    array('uses' => 'CompanyController@getCompanies')
);

/*
 *  GET http://reservation.hostname/{cluster_name}/companies/{id}
 *  returns the company with the specific id if it exists in the cluster
 */
Route::get(
    '/{cluster}/companies/{id}',
    array('uses' => 'CompanyController@getCompany')
);
