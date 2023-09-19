<?php
/**
 * @author Edwards
 * @copyright 2012
 */

namespace EXSQL;

if(!defined('FIELD_COL'))     define('FIELD_COL',0);
if(!defined('FIELD_EXP')) define('FIELD_EXP',1);
if(!defined('FIELD_POS')) define('FIELD_POS',2);

class order {
    private $data = array();
    public function clear() {
        $this->data= array();
        return $this;
    }
    
    public function thenby($field, $order='') {
        $this->by($field, $order);
        return $this;
    }
    
    private function by($field, $order='') {
        if(!empty($order)){
            $order = strtoupper(substr($order,0,3));
            if($order=='A' || $order=='AS') $order ='ASC';
            if($order=='D' || $order=='Z' || $order=='DES') $order ='DESC';
            if(!in_array($order,array('ASC','DESC'))) $order='';
        }
        if(is_null($field) || STRTOUPPER($field)=='NULL'){
            $this->data[] = "$field";
        }elseif(empty($order)){
            $this->data[] = "`$field`";
        }else{
            $this->data[] = "`$field` $order";
        }
    }
    public function __construct() {
    }
    
    /*public function __invoke($obj) {
        if($obj)
    }*/

    public function raw() {
        return implode(', ', $this->data);
    }
    public function __toString() {
        $w = $this->raw();
        if($w)
            return "ORDER BY $w";
        else
            return '';
        
    }
    public static function isOrder($value){
        $order = strtoupper(substr($value,0,3));
        if($order=='A' || $order=='AS') $order ='ASC';
        if($order=='D' || $order=='Z' || $order=='DES') $order ='DESC';
        if(!in_array($order,array('ASC','DESC'))) $order='';
        return in_array($order,array('ASC','DESC'));
    }
}
class orderItem{
    var $expr ='';
    var $alias ='';
    var $type =FIELD_COL;
    public function raw() {//null
        if($this->expr == '') return '';
        if(func_num_args())
            $type = func_get_arg(0);
        else
            $type =$this->type; 
        if($type == FIELD_COL){
            if($this->alias){
                return "`$this->expr` AS $this->alias";
            }else{
                return "`$this->expr`";
            }
        }else{
            if($this->alias){
                return "$this->expr AS $this->alias";
            }else{
                return "$this->expr";
            }
        }
    }
    public function __toString(){
        return $this->raw();
    }
}
