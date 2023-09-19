<?php
/**
 * @author Edwards
 * @copyright 2020
 */
namespace Compago\Database\Core;

class PredicateBag{
    protected $options = [];
    
    
    public function __toString() {
        return $this->toString();
    }
    public function toArray() {
        return $this->options;
    }
    public function clear() {
        $this->options = array();
        return $this;
    }
    public function delete($predicate){
        $ex = explode(' ', strtoupper($predicate));
        $this->options = array_diff($this->options,$ex);
        return $this;
    }
    public function set($predicate){
        $ex = explode(' ', strtoupper($predicate));
        $this->options = array_unique($ex);
        return $this;
    }
    public function add($predicate){
        $ex = explode(' ', strtoupper($predicate));
        $this->options = array_unique(array_merge($this->options,$ex));
        return $this;
    }
    public function toString(){
        return implode(' ',$this->options);
    }
    
}
