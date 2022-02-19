<?php

namespace BarTab\Models;

use BarTab\Exceptions\IdentificationEmptyOrNullException;
use ReflectionProperty;
/**
 * Base
 *
 * Base model using DOMDocument to access xml data
 *
 * @package    BarTab
 * @subpackage Models
 * @author     philippe <lukengup@aim.com>
 * @see \DOMDocument
 */

abstract class Base 
{

    private string $uri;
    private \DomXpath $xpath;
    private \DOMDocument $doc;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->doc = new \DOMDocument('1.0', 'utt-8');
        $this->doc->formatOutput = true;
        $this->doc->preserveWhiteSpace = false;


        $this->uri = dirname(__DIR__, 2)."/data/bartab.xml";
        $this->init();
    }

    private function  init(): void
    {
        if(file_exists($this->uri))
        {
            $this->doc->load($this->uri);
        } else
        {
            $this->doc->loadXML('<?xml version="1.0" encoding="utf-8" ?><data/>');
            $this->save();
            $this->doc->load($this->uri);
        }
        $this->xpath = new \DomXpath($this->doc);
       
    }

    /**
     *  Storing a single record
     * @return $this
     */
    public function store():Base
    {
        $parent_node = $this->doc->getElementsByTagName('data');
        $parent_node =  $parent_node->item(0);

        $data = $this->toArray();


        if(!isset($data['id']))
        {
            $data['id'] = uniqid('', false);
        }

        $node = $this->doc->createElement($this);

        foreach($data as $key => $value)
        {
            if(property_exists($this, $key))
            {
                $node->setAttribute($key, $value);
            }
        }
        $parent_node->appendChild($node);
        $this->doc->appendChild($parent_node);
        $this->save();

        $this->id = $data['id'];

        return $this;

    }

    /**
     * Updating a  single record
     */
    public function update(): void
    {

        $child_node = $this->xpath->query("//data/{$this}[@id='{$this->id}']");
        $data = $this->toArray();
        $element =  $child_node->item(0);

        foreach($data as $key => $value)
        {
            $element->setAttribute($key, $value);
        }

        $this->save();

    }

    /**
     * Retrieving All record o for a node
     *
     * @return array
     */
    public function all(): array
    {
        $array = [];

        foreach ($this->doc->getElementsByTagName($this) as $child_node)
        {
            if($child_node instanceof \DOMNode)
            {
                $attributes = $child_node->attributes;
                $reflection = new \ReflectionClass($this);
                $class_name = $reflection->getName();
                $item = new $class_name;
                foreach ($attributes as $attribute)
                {
                    $item->{$attribute->name} = $attribute->value;
                }
                //unset parent class property
                unset($item->xpath);
                unset($item->doc);
                unset($item->uri);

                $array[] = $item;
            }
        }
        return $array;

    }

    /**
     * Find  all  for a  single condition
     *
     * @param string $node_name
     * @param $key
     * @param $value
     * @return array
     */
    public function findAll(string $node_name, $key, $value): array
    {
        $child_nodes = $this->xpath->query("//data/{$node_name}[@{$key}='{$value}']");
        $array = [];
        foreach ($child_nodes as $child_node)
        {
            if($child_node instanceof \DOMElement)
            {

                $reflection = new \ReflectionClass($this);
                $class_name = $reflection->getName();
                $item = new $class_name;

                $attributes = $child_node->attributes;
                foreach ($attributes as $attribute)
                {
                    if(property_exists($this, $attribute->name))
                    {
                        $item->{$attribute->name} = $attribute->value;
                    }
                    //unset parent class property
                    unset($item->xpath);
                    unset($item->doc);
                    unset($item->uri);
                }
                $array[] = $item;
            }

        }
        return $array;
    }

    /**
     * Finds by id
     *
     * @param string $id
     * @return $this
     */
    public  function find(string $id)
    {

        $child_node = $this->xpath->query("//data/{$this}[@id='{$id}']");
        $child_node = $child_node->item(0);
        if($child_node instanceof \DOMElement){
            $attributes = $child_node->attributes;
            foreach ($attributes as $attribute){
                $this->{$attribute->name} = $attribute->value;
            }
        }
        //unset parent class property

        return $this;
    }

    /**
     * Saving document
     */
    private function save()
    {
        $this->doc->save($this->uri);
    }
    public function __toString()
    {
        $reflection = new \ReflectionClass($this);
        return strtolower($reflection->getShortName());

    }

    /**
     * Array representation of the class
     *
     * @return array
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();
        $representation = [];
        foreach ($properties as $property)
        {
            try
            {
                $propertyReflexion = new ReflectionProperty($this, $property->name);
                $propertyReflexion->setAccessible( $property->name);
                $representation[$property->name] = $propertyReflexion->getValue($this);
            } catch (\ReflectionException $e)
            {

            }


        }
        return $representation;
    }

    /**
     * Magic methods
     */


    /**
     * @param $key
     * @return mixed|void
     */
    public function __get($key)
    {
        if(property_exists($this, $key))
        {
            $reflection = new ReflectionProperty($this, $key);
            $reflection->setAccessible($key);
            return $reflection->getValue($this);
        }
    }

    /**
     * @param $key
     * @param $value
     */

    public function __set($key, $value)
    {
        if(property_exists($this, $key))
        {
            $reflection = new ReflectionProperty($this, $key);
            $reflection->setAccessible($key);
            $reflection->setValue($this, $value);

        }

    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return property_exists($this, $key);
    }



}
