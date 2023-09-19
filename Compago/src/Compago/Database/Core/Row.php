<?php

namespace Compago\Database\Core;
//use JsonSerializable;
use \Compago\Contracts\Arrayable;
//use \Compago\Contracts\Jsonable;

class Row implements \IteratorAggregate, \Countable, Arrayable{
    private $_ = array();
    public function Read($name, $default=false) {
        $name = strtolower($name);
        if(isset($this->_[$name]) || array_key_exists($name,$this->_))
            return $this->_[$name];
        else
            return $default;
    }
    public function Seek($name, $default='') {
        $name = strtolower($name);
        if(!array_key_exists($name,$this->_))
            $this->_[$name] = $default;
        return $this->_[$name];
    }
    public function Assert($name, $default) {
        $name =strtolower($name);
        if(!isset($this->_[$name]) || empty($this->_[$name]))
            $this->_[$name] = $default;
        return $this->_[$name];
    }
    public function exists($name){
        $name = strtolower($name);
        return isset($this->_[$name])|| array_key_exists($name,$this->_);
    }
    public function isEmpty($name)
    {
        $name = strtolower($name);
        return empty($this->_[$name]);
    }
    public function delete($name)
    {
        unset($this->_[$name]);
    }
    public function toArray(){
        return $this->_;
    }
    public function __construct($data=array()) {
        if(func_num_args())
            $this->_ = array_change_key_case($data,CASE_LOWER);
    }
    public function __get($name) {
        $name = strtolower($name);
        if(array_key_exists($name,$this->_))
            return $this->_[$name];
        else
            return '';
    }
    public function __set($name, $value) {
        $name = strtolower($name);
        $this->_[$name] = $value;
    }
    public function __unset($name) {
        $name = strtolower($name);
        unset($this->_[$name]);
    }
    public function __isset($name) {
        $name = strtolower($name);
        return isset($this->_[$name]);
    }
    public function __toString() {
        return print_r($this,1);
    }
    public function __call($name, $arguments) {
        return $this->__get($name);
    }
    
    /**
     * Returns an iterator for headers.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_);
    }

    /**
     * Returns the number of headers.
     *
     * @return int The number of headers
     */
    public function count()
    {
        return \count($this->_);
    }
}

