<?php
/**
 * @author Edwards
 * @copyright 2020
 */
namespace Compago\Database\Core;

class SpParameterBag{
    
    protected $data =array();
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function add($name,$type,$access='IN'){
        if(!$name) return $this;
        $key = strtoupper($name);
        $access = strtoupper($access);
        if(!in_array($access,array('IN','OUT','INOUT'))) $access ='';
        $this->data[$key] = trim("{$access} $name $type");
        return $this;
    }
    public function delete($name){
        $key = strtoupper($name);
        unset($this->data[$key]);
        return $this;
    }
    public function toArray() {
        return $this->data;
    }
    public function toString() {
        $f=array();
        foreach($this->data as $name => $value){
            $f[] = "$value";
        }
        return implode(', ', $f);
    }
    public function __toString() {
        return $this->toString();
    }
}