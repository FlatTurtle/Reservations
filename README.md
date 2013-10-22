reservations
============

reservations api to reserve things (such as meeting rooms)


Routes that needs authentication are using HTTP BasicAuth for now. 

Requirements 
=============

PHP => 5.3
MySQL => 5.5

How-to
======

git clone git@github.com:FlatTurtle/Reservations.git
cd Reservations
php composer.phar install 
cd api
phpunit

! You have to change values in config/testing/database.php to test the app

Copyright and license
=====================

2013 - FlatTurtle

Code is licensed under AGPLv3
