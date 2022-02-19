# Bar Tab example Application 

This application is meant for excercise purpose only and  the front end is based on Slim Framework and a touch of jquery


## Install the Application

Copy the code anywhere on your local machine (assuming that the machine can run php and composer)
 
Run these commands from root of the project You will require PHP 7.3 or newer.

```bash
composer install
./vendor/bin/phpunit tests/TestBeerModel
cd public
php -S localhost:[PORT_NUMBER]
```

Replace `[PORT_NUMBER]` with the desired  free port number on your local computer

* Ensure `logs/` is web writable.
* Ensure `data/` is web writable.
* Ensure `cache/` is web writable.
* run http://localhost:[PORT_NUMBER] on your browser


That's it! Now Enjoy.
