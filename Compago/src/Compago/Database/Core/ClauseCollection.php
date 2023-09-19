<?php
/**
 * @author Edwards
 * @copyright 2020
 */
namespace Compago\Database\Core;

abstract class ClauseCollection{
    protected $clauses = [];
    abstract function toString();
    public function add($id,$clause){
        $this->clauses[$id] = $clause;
    }
    public function get($id){
        if (isset($this->clauses[$id])){
            return $this->clauses[$id];
        }
        return null;
    }
    public function has($id){
        return isset($this->clauses[$id]);
    }
    
    public function toArray(){
        return $this->clauses;
    }
    public function clear() {
        $this->clauses = [];
        return $this;
    }
    public function __toString() {
        return $this->toString();
    }
}