<?php

use BarTab\Controllers\IndexController;
use BarTab\Controllers\TabController;
use Slim\App;


return function (App $app) {
    $app->get('/', IndexController::class.':index');
    $app->post('/', IndexController::class.':index');
    $app->get('/tab', TabController::class.':index');
    $app->post('/tab', TabController::class.':index');
    $app->get('/tab-edit', TabController::class.':edit');
    $app->post('/tab-edit', TabController::class.':edit');
    $app->get('/tab-close', TabController::class.':close');
    $app->post('/tab-close', TabController::class.':close');
};



