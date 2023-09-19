<?php

namespace Compago\Database;

class PartialResult {
    //this oobject s used for return value of DML, DDL queries called via ->query();
    private $data =array();
    public function __construct($data =array()) {
        if(func_num_args()){
            if(!isset($data['result'])){
                $data['result'] = false;
            }
            $this->data = array_change_key_case($data,CASE_LOWER);;
        }
    }
    public function __call($name, $arguments) {
        $name = strtolower($name);
        if(isset($this->data[$name])){
            return $this->data[$name];
        }
        return false;
    }
    public function __get($name) {
        if(method_exists($this,$name)){
            return $this->$name();
        }
        $name = strtolower($name);
        if(isset($this->data[$name])){
            return $this->data[$name];
        }
        return false;
    }
    public function failed(){return !$this->result;}
    public function successful(){return $this->result;}
    public function affected(){return $this->affected_rows;}
    public function num_rows(){return $this->affected_rows;}
    public function fetch_all(){return array();}
    public function fetch_fields(){return array();}
    public function get_warnings (){return $this->warnings;}
}