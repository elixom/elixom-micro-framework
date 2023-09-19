<?php
/**
 * @author Edwards
 * @copyright 2010
 */
 
class ELI_object extends Compago\Tools\PropertyBag;
{
    
    public function Exists($name){
        return $this->has($name);
    }
    public function toArray(){
        return $this->data;
    }
    public function __construct($data=array()) {
        if(func_num_args() && is_array($data))
            $this->data = array_change_key_case($data,CASE_LOWER);
    }
    public function __get($name) {
        $name = strtolower($name);
        if(isset($this->data[$name]) || array_key_exists($name,$this->data))
            return $this->data[$name];
        if(method_exists($this,$name))
            return $this->$name();
        return '';
    }
    public function __toString() {
        return print_r($this,1);
    }
    public function __call($name, $arguments) {
        trigger_error("Call to method which does not exists $name");
        return false;
    }
    public static function __callStatic($name, $arguments) {
        trigger_error("Call to static method which does not exists $name");
        return false;
    }
}
