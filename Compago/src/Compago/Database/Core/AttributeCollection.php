<?php
/**
 * @author Edwards
 * @copyright 2020
 */
namespace Compago\Database\Core;

abstract class AttributeCollection{
    protected $columns = [];
    abstract function toString();
    abstract public function add($options = []);
    
    public function toArray(){
        return $this->columns;
    }
    public function clear() {
        $this->columns = [];
        return $this;
    }
    public function __toString() {
        return $this->toString();
    }
}