<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;
if(!defined('SET_ONE'))     define('SET_ONE',1);
if(!defined('SET_VALUES'))     define('SET_VALUES',2);

class set {
    private $type = 0;
    protected $data = array();
    public function type() {
        
        return $this->type;
    }
    public function add(expr $expr) {
        if(!($expr instanceof expr)){
            $a = $expr;
            $expr = new expr;
            $expr->left = $a;
            $expr->operator = '';
            $expr->right = '';
        }
        $expr->type('SET');
        $this->data[] = $expr;
        return $expr;
    }
    public function set($field, $value,$doEscape=true) {
        if($field instanceof expr){
            $expr = $field;
        }else{
            $expr = new expr;
            $expr->left = $field;
            $expr->operator = '=';
            if(is_null($value)){
                $expr->right = "NULL";
            }elseif($doEscape){
                $value = DBX::escape($value);
                $expr->right = "'$value'";
            }else{
                $expr->right = $value;
            }
        }
        $this->add($expr);
        return $expr;
    }
    
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function __construct() {
    }
    
    public function raw($name) {
        return implode(' ', $this->data);
    }
    public function __toString() {
        $w = $this->raw();
        if($w)
            return "SET $w";
        else
            return '';
        
    }
}
