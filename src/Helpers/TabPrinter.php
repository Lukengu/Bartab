<?php

namespace BarTab\Helpers;

use BarTab\Models\Beer;
use BarTab\Models\Item;
use BarTab\Models\Order;

class TabPrinter
{
    private Order $order;
    private array $items;
    private Tab $tab;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $item = new Item;
        $this->items = $item->findAll($item, "order_id", $this->order->id);
        $this->tab = new Tab( $this->items);
    }

    /**
     * close tab
     */
    public function closeTab(): void
    {
        $this->order->status = Tab::ORDER_STATUS_CLOSED;
        $this->order->update();
    }

    /**
     * String representation
     *
     * @return string
     */
    public function __toString()
    {

        // TODO: Implement __toString() method.
        $print_out = "---------------------------------------------------------\n";
        $print_out .= " Order  number ". $this->order->id."\n";
        $print_out .= "---------------------------------------------------------\n";
        $print_out .= "\t\tQty\t\tPrice\t\tTotal\n";
        $currency = "";
        foreach ($this->items as $item)
        {
            $beer = new Beer;
            $beer = $beer->find($item->beer_id);
            $currency = $beer->currency;

            $print_out .= $beer->name."\t\t".$item->qty."\t\t".$item->price."\t\t".$beer->currency." ".($item->qty * $item->price)."\n";
        }

        $print_out .= "---------------------------------------------------------\n";
        $print_out .= "Total  \t\t\t\t\t\t\n".$currency." ".$this->tab->getOrderTotal();

        if($this->order->split_number > 1) {
            $print_out .= "---------------------------------------------------------\n";
            $print_out .= "Splits";
            $print_out .= "---------------------------------------------------------\n";

            for($i =0; $i <  $this->order->split_number - 1; $i++)
            {
                $print_out .= ($i+1).".  \t\t\t\t\t\t\n".$currency." ".round($this->tab->getOrderTotal()/$this->order->split_number , 2);
            }

            $print_out .= "---------------------------------------------------------\n";

        }
        return $print_out;

    }



}
