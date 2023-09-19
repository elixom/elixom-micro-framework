<?php

namespace Compago;
/*
The model represents the data, and does nothing else. The model does NOT depend on the controller or the view.
*/

abstract class Model
{
    protected $table;
    public function __construct($a = [])
    {
        foreach ($a as $k => $v) {
            $this->$k = $v;
        }
    }
    public function __get($name){
        return '';
    }
    public function toArray(){
        $a = get_object_vars($this);
        if(!is_array($a)){
            $a = [];
        }
        if (func_num_args()){
            $f = func_get_arg(0);
            if (is_array($f) ){
                //$f = array_map('strtolower', $f);
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
    
}
