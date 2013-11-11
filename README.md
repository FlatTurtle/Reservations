reservations
============

[![Build Status](https://travis-ci.org/FlatTurtle/Reservations.png)](https://travis-ci.org/FlatTurtle/Reservations)

Reservations api to reserve things (such as meeting rooms)


Requirements 
=============

* PHP => 5.3
* MySQL => 5.5

How-to
======
```bash 
git clone git@github.com:FlatTurtle/Reservations.git
cd Reservations
php composer.phar install
# edit the database file
vim app/config/database.php
# and for testing purposes
vim app/config/testing/database.php
# run the tests
phpunit
# when deploying, be sure to chmod app/storage to 775
chmod -R 775 app/storage
# Finally, when doing a commit, please don't commit a filled out database.php!
``` 

! You have to change values in config/testing/database.php to test the app

Copyright and license
=====================

2013 - FlatTurtle

Code is licensed under AGPLv3
