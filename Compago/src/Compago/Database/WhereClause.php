<?php

namespace Compago\Database;

class WhereClause implements \ArrayAccess, \IteratorAggregate, \Countable{
    protected $type = 'AND';
    protected $exclusive = false;
    protected $items = array();
    public function add_clause($cond){
        $w =  new static();
        if($cond) $w->add($cond);
        if($cond) $this->items[] = $w;
        return $w;
    }
    public function add($cond){
        $cond =trim($cond);
        if($cond) $this->items[] = $cond;
        return $this;
    }
    public function clear(){
        $this->items= array();
        $this->exclusive = false;
        return $this;
    }
    public function set_exclusive($state=true){
        $this->exclusive = (bool)$state;
        return $this;
    }
    public function set_type($type,$exclusiveWHERE=null){ //sets the COND type not the operator type
        if(func_num_args()==2){
            $this->set_exclusive($exclusiveWHERE);
        }
        $type = strtoupper(trim($type));
        switch($type){
        case 'AND': $this->type ='AND'; return true;
        case 'OR': $this->type ='OR'; return true;
        case 'EAND': //there is no such thing as an Exclusice and 
        case 'XAND': $this->exclusive =true; $this->type ='AND'; return true;
        case 'XOR':   $this->type ='XOR'; return true;
        case 'EOR':  $this->exclusive =true; $this->type ='OR'; return true;
        }
        return false;
    }
    public function toString(){
        $wh = array();
        foreach($this->items as $seg){
            $b = (string)$seg;
            $b =trim($b);
            if($b) $wh[] = $b;
        }
        $b = implode(" {$this->type} ",$wh);
        if($b && $this->exclusive) $b = "($b)";
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
        return \count($this->items);
    }
}
