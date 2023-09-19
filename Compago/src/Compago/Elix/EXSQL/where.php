<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;
include_once('expr.php'); 

class where {
    private $data = array();
    // $operators = array('< ', '> ', '<=', '>=', '!=', '<>','IS');
    public function Between($column, $a , $b ) {
        $expr = new expr;
        $expr->left = $column;
        $expr->operator = 'BETWEEN';
        $expr->right = $a;
        $expr->right2 = $b;
        if($expr->type() != 'AND' && $expr->type() != 'OR') $expr->type('AND');
        $this->data[] = $expr;
        return $expr;
    }
    public function AddExpression(expr $expr) {
        if(!($expr instanceof expr)){
            $a = $expr;
            $expr = new expr;
            $expr->left = $a;
            $expr->operator = '';
            $expr->right = '';
        }
        if($expr->type() != 'AND' && $expr->type() != 'OR') $expr->type('AND');
        $this->data[] = $expr;
        return $expr;
    }
    public function AndExpression(expr $expr) {
         if(!func_num_args()) $expr = new expr;
         $this->data[] = $expr;
         $expr->type('AND');
         return $expr;
    }
    public function OrExpression(expr $expr) {
         if(!func_num_args()) $expr = new expr;
         $this->data[] = $expr;
         $expr->type('OR');
         return $expr;
    }
    public function AllAnd($field, $value=null,$operator='=',$doEscape=true) {
        $this->all('AND',$field, $value,$operator,$doEscape);
    }
    public function AllOr($field, $value=null,$operator='=',$doEscape=true) {
        
        $this->all('OR',$field, $value,$operator,$doEscape);
    }
    public function thatOr($field, $value=null,$operator='=',$doEscape=true) {
        $this->that('OR',$field, $value,$operator,$doEscape);
    }
    public function thatAnd($field, $value=null,$operator='=',$doEscape=true) 
    {
        $this->that('AND',$field, $value,$operator,$doEscape);
    }
    private function join($field, $value=null,$operator='=',$doEscape=true){
        $expr = new expr();
        $expr->operator = strtoupper(trim($operator));
        $expr->left = $field;
        
        if(substr($operator,0,2)=='IS' && (is_null($value) || strtoupper($value)=='NULL')){    
            $expr->right = 'NULL';
        }elseif(is_null($value)){
            $expr->operator = '';
            $expr->right = '';
        }else{
            if($doEscape){
                $value = DBX::escape($value);
                $expr->right =  "'$value'";
            }else{
                $c = substr($value,0,1);
                if($c == substr($value,-1) && ($c=='"'|| $c = "'")){
                    $expr->right = $value;
                }else{
                    $expr->right = $value;
                }   
            }
        }
        return $expr;
    }
    private function all($a='AND',$field, $value=null,$operator='=',$doEscape=true){
        $w = $this->raw();
        $this->data= array();
        if($w){
            $this->data[] = "($w)";
            $this->data[] = $a;
        }
        $this->data[] = $expr =$this->join($field, $value,$operator,$doEscape);
        $expr->type($a);
    }
    private function that($a='AND',$field, $value=null,$operator='=',$doEscape=true){
        $this->data[] = $expr =$this->join($field, $value,$operator,$doEscape);
        $expr->type($a);
    }
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function __construct() {
    }
    
    public function raw($name) {
        if($this->data[0] instanceof expr)
            $this->data[0]->type('');
            
        return implode(' ', $this->data);
    }
    public function __toString() {
        $w = $this->raw();
        if($w)
            return "WHERE $w";
        else
            return '';
        
    }
}

class whereXX {
    private $data = array();
    // $operators = array('< ', '> ', '<=', '>=', '!=', '<>','IS');
    
    public function AllAnd($field, $value=null,$operator='=',$doEscape=true) {
        $this->all('AND',$field, $value,$operator,$doEscape);
    }
    public function AllOr($field, $value=null,$operator='=',$doEscape=true) {
        
        $this->all('OR',$field, $value,$operator,$doEscape);
    }
    public function thatOr($field, $value=null,$operator='=',$doEscape=true) {
        $this->that('OR',$field, $value,$operator,$doEscape);
    }
    public function thatAnd($field, $value=null,$operator='=',$doEscape=true) 
    {
        $this->that('AND',$field, $value,$operator,$doEscape);
    }
    private function join($field, $value=null,$operator='=',$doEscape=true){
        
        $operator = strtoupper(trim($operator));
        if(substr($operator,0,2)=='IS' && (is_null($value) || strtoupper($value)=='NULL')){
            return "`$field` $operator NULL";
        }elseif(is_null($value)){
            return "`$field`";
        }else{
            if($operator=='==') $operator = '=';
            if($doEscape){
                $value = DBX::escape($value);
                return "`$field` $operator '$value'";
            }else{
                $c = substr($value,0,1);
                if($c == substr($value,-1) && ($c=='"'|| $c = "'")){
                    return "`$field` $operator $value";
                }else{
                    return "`$field` $operator $value";
                }   
            }
            
        }
    }
    private function all($a='AND',$field, $value=null,$operator='=',$doEscape=true){
        $w = $this->raw();
        $this->data= array();
        if($w){
            $this->data[] = "($w)";
            $this->data[] = $a;
        }
        $this->data[] = $this->join($field, $value,$operator,$doEscape);
    }
    private function that($a='AND',$field, $value=null,$operator='=',$doEscape=true){
        if(count($this->data)){
            $this->data[] = $a;
        }
        $this->data[] = $this->join($field, $value,$operator,$doEscape);
        
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
            return "WHERE $w";
        else
            return '';
        
    }
}

?>