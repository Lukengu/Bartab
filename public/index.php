<?php
require __DIR__ . '/../vendor/autoload.php';
// Instantiate PHP-DI ContainerBuilder
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use \BarTab\Middleware\SessionMiddleware;

$containerBuilder = new ContainerBuilder();
// Add container definition for the flash component
$containerBuilder->addDefinitions(
    [
        'flash' => function () {
            $storage = [];
            return new Messages($storage);
        }
    ]
);



$containerBuilder->enableCompilation(__DIR__ . '../cache');

// Build PHP-DI Container instance

$settings = require __DIR__ . '/../src/settings.php';



// Set up dependencies
$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($containerBuilder, $settings);




$container = $containerBuilder->build();





// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->add(function ($request, $next) {
    // Start PHP session
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    // Change flash message storage
    $this->get('flash')->__construct($_SESSION);

    return $next->handle($request);
});

$app->addErrorMiddleware(true, true, true);


// Register routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);


$app->run();










