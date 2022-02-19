<?php

namespace BarTab\Helpers;

use BarTab\Exceptions\AlreadyClosedException;
use BarTab\Models\Beer;
use BarTab\Models\Order;
use BarTab\Models\Item;

/**
 * Tab
 *
 *  Main helper class all logic behnind the app
 *
 * @package    BarTab
 * @subpackage Helpers
 * @author     philippe <lukengup@aim.com>
 *
 */

class Tab
{

    private array $items;
    public const ORDER_STATUS_OPEN= 'TAB_OPENED';
    public const  ORDER_STATUS_CLOSED= 'TAB_CLOSED';

    /**
     * Conxtruct with item in the Tab
     * @param array $items
     */
    public function  __construct(array  $items)
    {
        $this->items = $items;
    }

    /**
     * Creating  a new order
     *
     * @param int $split
     * @return Order
     */
    public function  createNewOrder($split = 1) : Order
    {

        $order = new Order();
        $order->status = self::ORDER_STATUS_OPEN;
        $order->number_split = $split;
        $order->total = $this->getOrderTotal();
        $order = $order->store();

        foreach ($this->items as $i)
        {
            $item = new Item();
            $item->order_id = $order->id;
            $item->qty = $i->qty;
            $item->beer_id = $i->beer_id;
            $item->store();

        }

        return $order;

    }

    /**
     * Updating existing order
     *
     * @param string $order_id
     * @param array $new_items
     * @param int $split
     * @return array
     * @throws AlreadyClosedException
     */

    public function  addTOrder(string $order_id, array $new_items, int $split = 0): array
    {
        $order = new Order();
        $order = $order->find($order_id);
       


        if($order->status === self::ORDER_STATUS_CLOSED)
        {
            throw  new AlreadyClosedException("The tab is already closed");
        }

        if($split !== 0) 
        {
            $order->number_split = $split;
            $order->update();
        }
        unset($order);


        $item = new Item;
        $this->items = $item->findAll($item, "order_id", $order_id);



        //Updating existing beer  in the tab
        array_walk($this->items, function(&$value, $key) use (&$new_items) 
        {

            $filtered = array_filter($new_items, function ($new_item) use($value) {
                return $new_item->beer_id == $value->beer_id;
            });

            if($filtered) 
            {
                $new_item = $filtered[0];
                $value->qty += $new_item->qty;
                $index = array_search($new_item, $new_items);
            
                if(isset($new_items[$index]))
                    unset($new_items[$index]);

            }

        });



        //Updating existing Items
        foreach($this->items as $existing_item)
        {
            $item = $item->find($existing_item->id);
            $item->qty = $existing_item->qty;
            $item->update();

        }

        //Adding beer if not in the tab already
        if($new_items)
        {
            foreach($new_items as $new_item)
            {
                $item = new Item();
                $item->order_id = $order_id;
                $item->qty = $new_item->qty;
                $item->beer_id = $new_item->beer_id;

                $this->items[] = $item->store();


            }

        }

        $order = new Order();
        $order = $order->find($order_id);
    
        $order->total = $this->getOrderTotal();
        $order->update();

        return $this->items;

    }

    /**
     * Total order
     *
     * @return float|int
     */
    public function getOrderTotal()
    {
        $total = 0;
      
        foreach ($this->items as $item) 
        {
            $beer = new Beer;
            $beer = $beer->find($item->beer_id);
            $total += $beer->price * $item->qty;
        }
        return $total;
    }
}
