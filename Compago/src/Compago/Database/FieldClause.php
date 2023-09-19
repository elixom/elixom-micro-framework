<?php

namespace Compago\Database;

class FieldClause implements \ArrayAccess, \IteratorAggregate, \Countable{
    protected $distinct = false;
    protected $items = array();
    
    public function add($cond){
        if(func_num_args()>1){
            $a = func_get_args();
            foreach($a as $f) $this->add($f);
            return $this;
        }
        if(is_array($cond)){
            foreach($cond as $f) $this->add($f);
            return $this;
        }
        $cond =trim($cond);
        if($cond) $this->items[] = $cond;
        return $this;
    }
    public function add_as($field,$as){
        $as =trim($as);
        if($as)
            $this->add("$field AS $as");
        else
            $this->add($field);
        return $this;
    }
    public function add_count($field,$as=''){
        if(!$field) $field ='*';
        $this->add_as("COUNT($field)", $as);
        return $this;
    }
    public function add_count_distinct($field,$as=''){
        if(!$field) $field ='*';
        $this->add_as("COUNT(DISTINCT $field)", $as);
        return $this;
    }
    public function add_function($fx,$field,$as=''){
        $fx = strtoupper(trim($fx));
        $fx = trim($fx,')(');
        if(!$fx) return $this->add_as($field,$as);
        if(!$field) return $this;
        $this->add_as("$fx($field)", $as);
        return $this;
    }
    
    public function set_distinct($state=true){
        $this->distinct = (bool)$state;
        return $this;
    }
    public function clear(){
        $this->items= array();
        return $this;
    }
    
    public function toString(){
        $wh = array();
        foreach($this->items as $seg){
            $b = (string)$seg;
            $b =trim($b);
            if($b) $wh[] = $b;
        }
        $b = implode(", ",$wh);
        if($this->distinct) $b = "DISTINCT $b";
        return $b;
    }
    public function __construct() {
        if(func_num_args()){
            $a = func_get_arg(0);
            if(is_string($a)){
                $b = strtoupper(trim($a));
                if(!$this->set_type($b)){
                    $this->add($a);
                }
            }
        }
    }

    public function __toString() {
        return $this->toString();
    }
    public function offsetSet($offset,$value) {
        if ($offset == '') {
            $this->items[] = $value;
        }else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * Returns an iterator for headers.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function count() {
        return count($this->items);
    }
}

