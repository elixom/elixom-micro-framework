<?php
/**
 * @author Edwards
 * @copyright 2018
 * 
 * 
 */
namespace Compago\Traits;

trait PropertyBag{
    protected $data = array();
    public function id()
    {
        if (isset($this->data['id'])) return $this->data['id'];
        if(defined('static::ID_FIELD')) {
            $field = static::ID_FIELD;
            if (isset($this->data[$field])) return $this->data[$field];
            return 0;
        }
        return false;
    }
    public function toArray(){
        $a = $this->data;
        if(!is_array($a)){
            $a = [];
        }
        if (func_num_args()){
            $f = func_get_arg(0);
            if (is_array($f) ){
                $f = array_map('strtolower', $f);
                foreach ($a as $name=>$v){
                    if (!in_array($name,$f)) unset($a[$name]);
                }
                foreach ($f as $name){
                    if (!isset($a[$name])){
                        if (($v = $this->__get($name)) !== null){
                            $a[$name] = $v;
                        }
                    }
                }
            }
        }
        return $a;
    }
    public function toJsonString(){
        $a = $this->data;
        if(!is_array($a)){
            $a = [];
        }
        if (func_num_args()){
            $f = func_get_arg(0);
            if (is_array($f) ){
                $f = array_map('strtolower', $f);
                foreach ($a as $name=>$v){
                    if (!in_array($name,$f)) unset($a[$name]);
                }
                foreach ($f as $name){
                    if (!isset($a[$name])){
                        if (($v = $this->__get($name)) !== null){
                            $a[$name] = $v;
                        }
                    }
                }
            }
        }
        return json_encode($a);
    }
    public function __construct($data=array()) {
        if(func_num_args() && is_array($data))
            $this->data = array_change_key_case($data,CASE_LOWER);
    }
    public function get($name,$default=null) {
        $name = strtolower($name);
        if(isset($this->data[$name]) || array_key_exists($name,$this->data))
            return $this->data[$name];
        return $default;
    }
    /**
     * Returns the parameter value converted to integer.
     *
     * @param string $key     The parameter key
     * @param int    $default The default value if the parameter key does not exist
     *
     * @return int The filtered value
     */
    public function getInt($key, $default = 0)
    {
        return (int) $this->get($key, $default);
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
}