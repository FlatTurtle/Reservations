Reservations
============

[![Build Status](https://travis-ci.org/FlatTurtle/Reservations.png)](https://travis-ci.org/FlatTurtle/Reservations)

Reservations api to reserve things (such as meeting rooms)

## Vocabulary

* Thing: something that can be reserved. E.g., a meeting room.
* Amenity: something that can be added to a reservation. E.g., wifi.
* Reservation: a user reservation

## Requirements 

* PHP => 5.3+
* MySQL => 5.5

## Installing

```bash 
git clone git@github.com:FlatTurtle/Reservations.git
cd Reservations
php composer.phar install
# when deploying, be sure to chmod app/storage to 777
chmod -R 777 app/storage
# create a database for development purposes and add the credentials over here:
vim app/config/local/database.php
# Now add your hostname to the array in this file:
vim bootstrap/start.php
```

Finally, when doing a commit, please don't commit a filled out local/database.php!

Using artisan to add and delete stuff
=====================================

You can use artisan to generate the right parameters for a HTTP request towards the API.

Usage:

```bash
./artisan reservations:addUser

./artisan reservations:addThing

//... todo
```

## Testing

You can run the unittests by creating a mysql database called `reservations_test`.
A user called `travis` should have access and the password should be blank.

```bash
# create a database for testing purposes and add the credentials over here:
vim app/config/testing/database.php
# run the tests
phpunit
```

For more questions, consult the wiki on github.

## Copyright and license

2013 - FlatTurtle

Code is licensed under AGPLv3
