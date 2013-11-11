reservations
============

[![Build Status](https://travis-ci.org/FlatTurtle/Reservations.png)](https://travis-ci.org/FlatTurtle/Reservations)

Reservations api to reserve things (such as meeting rooms)


Requirements 
=============

* PHP => 5.3
* MySQL => 5.5

Installing and testing
======================
```bash 
git clone git@github.com:FlatTurtle/Reservations.git
cd Reservations
php composer.phar install
# when deploying, be sure to chmod app/storage to 777
chmod -R 777 app/storage
# create a database for testing purposes and add the credentials over here:
vim app/config/testing/database.php
# run the tests
phpunit
# create a database for development purposes and add the credentials over here:
vim app/config/local/database.php
# Now add your hostname to the array in this file:
vim bootstrap.php
# Finally, when doing a commit, please don't commit a filled out local/database.php!
``` 

Copyright and license
=====================

2013 - FlatTurtle

Code is licensed under AGPLv3
