<?php

namespace BarTab\Controllers;
use Psr\Container\ContainerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;


class IndexController
{


    private $twig;

    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    public function index(Request $request, Response $response)
    {
        $response->getBody()->write( $this->twig->render('index.twig'));
        return $response;

    }

}
