<?php

use Monolog\Logger;

return [
    'displayErrorDetails' => true, // Should be set to false in production
    'logError' => false,
    'logErrorDetails' => false,
    'addContentLengthHeader' => false, // Allow the web server to send the content-length header
    'renderer' => [
        'template_path' => __DIR__ . '/../templates/',
    ],
    'app_env' => 'DEVELOPMENT',
    'view' => [
        'template_path' => __DIR__ . '/../templates',
        'twig' => [
            'cache' => __DIR__ . '/../cache/twig',
            'debug' => true,
            'auto_reload' => true,
        ],
    ],
    'logger' => [
        'name' => 'slim-app',
        'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
        'level' => Logger::DEBUG,
    ],
];
