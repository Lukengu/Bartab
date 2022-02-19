<?php

namespace BarTab\Controllers;
use BarTab\Models\Beer;
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

        if(strtolower($request->getMethod()) === 'post')
        {
            //Populate default data
            $records = [
                ['name' => "Lager", 'price' => 45.00],
                ['name' => "IPA", 'price' => 52.00],
                ['name' => "Weissbier", 'price' => 59.00]
            ];
            foreach($records as $record)
            {
                $beer = new Beer;
                foreach ($record as $key => $value)
                {
                    $beer->{$key} = $value;
                }
                $beer->store();
            }
            $response->withStatus(301)->withHeader("Location", '/');
        }
        $beer = new Beer;
        $beers = $beer->all();
        $response->getBody()->write( $this->twig->render('index.twig', ['beers' => $beers]));
        return $response;

    }

}
