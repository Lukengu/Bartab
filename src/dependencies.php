<?php
// config/dependencies.php
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;

return static function (ContainerBuilder $containerBuilder, array $settings) {
    $containerBuilder->addDefinitions([
        'settings' => $settings,

        LoggerInterface::class => function (ContainerInterface $c): Logger {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        \Twig\Environment::class => function (ContainerInterface $c) use ($settings) {
            $loader = new Twig\Loader\FilesystemLoader($settings['view']['template_path']);
            $twig = new Twig\Environment($loader, [
                $settings['view']['twig']['cache']
            ]);
            if ($settings['app_env'] === 'DEVELOPMENT') {
                $twig->enableDebug();
            }
            $twig->addGlobal('flash', $c->get(Messages::class));

            return $twig;
        }

    ]);
};
