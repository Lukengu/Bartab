<?php

namespace BarTab\Controllers;

use BarTab\Exceptions\AlreadyClosedException;
use BarTab\Helpers\Tab;
use BarTab\Helpers\TabPrinter;
use BarTab\Models\Beer;
use BarTab\Models\Item;
use BarTab\Models\Order;
use DI\Container;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class TabController
{
    private $twig;
    private $container;

    public function __construct(\Twig\Environment $twig, Container  $container)
    {
        $this->twig = $twig;
        $this->container = $container;
    }

    public function index(Request $request, Response $response)
    {
        $beer = new Beer();
        $beers = $beer->all();

        if(strtolower($request->getMethod()) === 'post')
        {
            $post = $request->getParsedBody();
            $beer_data = $post['beer_id'];
            $total = 0;
            $items = [];
            foreach($beer_data as $id => $qty)
            {
                if(is_numeric($qty))
                {
                    $total += $qty;
                    $item  = new Item;
                    $item->beer_id = $id;
                    $item->qty = $qty;
                    $items[] = $item;
                }
            }

            if($total == 0)
            {
                $response->getBody()->write( $this->twig->render('tab/index.twig', ['beers' => $beers,
                    'messages' => ['danger' => 'Your tab is empty' ] ]));
            }

            $split_number = $post['split_number'] ?? 1;
            $tab_helper = new Tab($items);
            $order = $tab_helper->createNewOrder($split_number);

            $response->getBody()->write( $this->twig->render('tab/index.twig', ['beers' => $beers,
                'messages' => ['success' => 'Your order number is '.$order->id.' you will need it to query the tab']]));

        }

        $response->getBody()->write( $this->twig->render('tab/index.twig', ['beers' => $beers]));
        return $response;

    }

    public function edit(Request $request, Response $response)
    {
        $beer = new Beer();
        $beers = $beer->all();
        $data['beers'] = $beers;
        $currency = $beers[0]->currency;
        $data['currency'] = $currency;

        if(strtolower($request->getMethod()) === 'post')
        {
            $post = $request->getParsedBody();
            $order_id =  $post['order_id'];
            $total = 0;

            if(isset($post['beer_id']))
            {
                $items = [];
                foreach($post['beer_id'] as $id => $qty)
                {
                    if(is_numeric($qty))
                    {
                        $total += $qty;
                        $item  = new Item;
                        $item->beer_id = $id;
                        $item->qty = $qty;
                        $items[] = $item;
                    }
                }

                if($total > 0 )
                {
                    $tab = new Tab($items);
                    $tab->addTOrder($order_id, $items, $post['split_number']);
                    $data['messages'] = ['success' => 'You tab has been updated'];
                }

            }

            try
            {
                $order = new Order();
                $order = $order->find($order_id);
                if($order->status == Tab::ORDER_STATUS_CLOSED)
                {
                    throw new AlreadyClosedException("Order  #{$order->id} already closed");
                }
                $data['order'] = $order;

                $item = new Item();
                $items = $item->findAll($item, 'order_id', $order_id);
                $tab = new Tab($items);
                $data['order_total'] = $tab->getOrderTotal();

                if ($order->number_split > 1) {
                    $data['split_amount'] = round($tab->getOrderTotal() / $order->number_split, 2);
                }
                array_walk($items, [$this, '__']);
                $data['items'] = $items;
            } catch( AlreadyClosedException $ex)
            {
                $data['messages'] = ['danger' => $ex->getMessage()];
                $response->getBody()->write( $this->twig->render('tab/edit.twig', $data));
                return $response;
            }

            if($total == 0 && isset($post['beer_id']))
            {
                $data['messages'] = ['danger' => 'Your tab is empty'];
                $response->getBody()->write( $this->twig->render('tab/edit.twig',  $data));
                return $response;
            }

        }
        $response->getBody()->write( $this->twig->render('tab/edit.twig', $data));
        return $response;

    }

    private function __(&$value, $key)
    {
        $beer_id = $value->beer_id;
        $beer = new Beer;
        $beer = $beer->find($beer_id);
        $value->beer_id = $beer;




    }

    public function close(Request $request, Response $response)
    {
        $data = [];
        if(strtolower($request->getMethod()) === 'post')
        {
            $post = $request->getParsedBody();
            $order_id =  $post['order_id'];


            try {
                $order = new Order();
                $order = $order->find($order_id);
                if($order->status == Tab::ORDER_STATUS_CLOSED)
                {
                    throw new AlreadyClosedException("Order  #{$order->id} already closed");
                }
                if(isset($post['close']))
                {
                    $tab_printer = new TabPrinter($order);
                    $tab_printer->closeTab();
                    $data['messages'] = ['success' => 'The tab has been closed'];
                    $response->getBody()->write( $this->twig->render('tab/close.twig', $data));
                    return $response;

                }


                $data['order'] = $order;

                $beer = new Beer();
                $beers = $beer->all();
                $currency = $beers[0]->currency;
                $data['currency'] = $currency;

                $item = new Item();

                $items = $item->findAll($item, 'order_id', $order_id);
                $tab = new Tab($items);
                $data['order_total'] = $tab->getOrderTotal();

                if ($order->number_split > 1) {
                    $data['split_amount'] = round($tab->getOrderTotal() / $order->number_split, 2);
                }
                array_walk($items, [$this, '__']);
                $data['items'] = $items;

            } catch( AlreadyClosedException $e){
                $data['messages'] = ['danger' => $e->getMessage()];
                $response->getBody()->write( $this->twig->render('tab/close.twig', $data));
                return $response;

            }

        }
        $response->getBody()->write( $this->twig->render('tab/close.twig', $data));
        return $response;


    }

}
