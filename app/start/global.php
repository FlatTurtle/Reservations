<?php

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

        app_path().'/commands',
        app_path().'/providers',
        app_path().'/validators',
        app_path().'/exceptions',
        app_path().'/controllers',
        app_path().'/models',
        app_path().'/database/seeds',

));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a rotating log file setup which creates a new file each day.
|
*/

$logFile = 'log-'.php_sapi_name().'.txt';

Log::useDailyFiles(storage_path().'/logs/'.$logFile);

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

App::error(function(Exception $exception, $code)
{
    Log::error($exception);
});

App::error(function(EntityNotFoundException $exception, $code){
    return Response::json(array(
        "success" => 0,
        "errors" => array(
            array(
                "code" => $exception->getCode(),
                "type" => $exception->getType(),
                "message" => Lang::get("errors." . $exception->getType())
            )
        )
    ), $exception->getCode());
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenace mode is in effect for this application.
|
*/

App::down(function()
{
        return Response::make("Be right back!", 503);
});

Validator::extend('schema', 'EntityValidator@validateSchema');
Validator::extend('schema_type', 'EntityValidator@validateSchemaType');
Validator::extend('hours', 'EntityValidator@validateHours');
Validator::extend('opening_hours', 'EntityValidator@validateOpeningHours');
Validator::extend('price', 'EntityValidator@validatePrice');
Validator::extend('map', 'EntityValidator@validateMap');
Validator::extend('location', 'EntityValidator@validateLocation');
Validator::extend('amenities', 'EntityValidator@validateAmenities');
Validator::extend('body', 'EntityValidator@validateBody');
Validator::extend('time', 'ReservationValidator@validateTime');
Validator::extend('customer', 'ReservationValidator@validateCustomer');
/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';

Route::filter('auth.basic', function()
{
    Config::set('auth.model', 'Cluster');
    Auth::basic('clustername');
    if (Auth::guest()) {
        return Response::json(array(
              "success" => 0,
              "errors" => array(
                array(
                  "code" => 401,
                  "type" => "Invalid credentials",
                  "message" => "The credentials you provided are invalid."
                )
              )
            ), 401);
    }
    return;
});



use Hautelook\Phpass\PasswordHash;
use Illuminate\Auth\Guard;
Auth::extend('flatturtle_phpass', function($app)
{
    $hasher = new PasswordHash(8,false);
    return new Guard(
        new FlatTurtleClusterProvider($hasher, 'Cluster'),
        $app['session.store']
    );
});
