<?php
require_once __DIR__ . '/../vendor/autoload.php';


use PHPUnit\Framework\TestCase;
use BarTab\Models\Beer;
use BarTab\Helpers\Tab;
use BarTab\Models\Order;
use BarTab\Models\Item;

final class TestBeerModel extends  TestCase {

    /**
     * Assuming that the xml data does not exist we expect 3 records
     */
    public function testCreate() {

        $beer = new Beer();
        $beer->name = "Lager";
        $beer->price =  45.00;
        $beer->store();

        $beer = new Beer();
        $beer->name = "IPA";
        $beer->price =  52.00;
        $beer->store();

        $beer = new Beer();
        $beer->name = "Weissbier";
        $beer->price =  59.00;
        $beer->store();


        $this->assertCount(3, $beer->all());

    }

    public function testMakingOrder() {
        $beer = new Beer;
        $beers = $beer->all();

        $items = [];

        $item = new Item;
        $item->qty = 2;
        $item->beer_id = $beers[0]->id;

        $items[] = $item;

        $item = new Item;
        $item->qty = 3;
        $item->beer_id = $beers[1]->id;

        $items[] = $item;

        $tab = new Tab($items);
        $tab->createNewOrder(3);

        $this->assertEquals(246, $tab->getOrderTotal());

    }


   public function testUpdatingOrder() {
        $order = new Order;
        $order = $order->findAll($order, 'status', Tab::ORDER_STATUS_OPEN);
        $order = $order[0];

        $beer = new Beer;
        $beers = $beer->all();


        $items = [];

        $item = new Item;
        $item->qty = 3;
        $item->beer_id = $beers[0]->id;
        $items[] = $item;

        $item = new Item;
        $item->qty = 1;
        $item->beer_id = $beers[2]->id;
        $items[] = $item;
        
        $tab = new Tab($items);

        try {
            $tab->addTOrder($order->id, $items, 4);
            $this->assertEquals(440, $tab->getOrderTotal());

        } catch(\BarTab\Exceptions\AlreadyClosedException $ex){}

    }
    
  



}
