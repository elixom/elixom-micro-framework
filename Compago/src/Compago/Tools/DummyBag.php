<?php
/**
 * @author Edwards
 * @copyright 2018
 * 
 * 
 */
namespace Compago\Tools;

class DummyBag
{
    protected $data = array();
    
    public function __construct($data=array()) {
        if(func_num_args() && is_array($data))
            $this->data = array_change_key_case($data,CASE_LOWER);
    }
    public function __get($name) {
        $name = strtolower($name);
        if(isset($this->data[$name]) || array_key_exists($name,$this->data))
            return $this->data[$name];
        return '';
    }
    public function __set($name, $value) {
        $name = strtolower($name);
        if((null ===$value))
            unset($this->data[$name]);
        else
            $this->data[$name] = $value;
    }
    public function __unset($name) {
        $name = strtolower($name);
        unset($this->data[$name]);
    }
    public function __isset($name) {
        $name = strtolower($name);
        return isset($this->data[$name]);
    }
    public function __call($name,$args) {
        $this->data["->$name"] = $args;
        error_log("DummyBag->{$name}");
    }
    public static function __callStatic($name,$args) {
        error_log("DummyBag::{$name}");
    }
}