<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;
include_once('fields.php');

class values {
    private $data = array();
    
    public function add($values='') {
        $field = new valueSet();
        if(func_num_args()==1 && is_array($values)){
            
        }else{
            $values = func_get_args();
        }
        foreach($values as $expr)
            $field->addExpression($expr);
        $this->data[] = $field;
        return $field;
    }
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function __construct() {
    }
    
    public function raw() {
        $r =array();
        foreach($this->data as $field){
                $f[] = "$field";
        }
        $f = array_filter($f);
        return implode(', ', $f);
    }
    public function __toString() {
        if(count($this->data) == 0){
            return "VALUES ()";
        }
        $w = $this->raw();
        if($w)
            return "VALUES $w";
        else
            return '';
    }
}
class valueSet{
    private $data = array();
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function addColumn($fieldName) {
        $field = new fieldItem();
        $field->type = FIELD_COL;
        if(is_array($fieldName)){
            $c = count($fieldName);
            if($c==1)
                $field->expr = trim(array_unshift($fieldName),'`');
            elseif($c==2){
                $field->tablename = trim(array_unshift($fieldName),'`');
                $field->expr = trim(array_unshift($fieldName),'`');
            }elseif($c==3){
                $field->dbname = trim(array_unshift($fieldName),'`');
                $field->tablename = trim(array_unshift($fieldName),'`');
                $field->expr = trim(array_unshift($fieldName),'`');
            }
        }else
            $field->expr = trim($fieldName,'`');
        $field->alias = '';  
        $this->data[] = $field;
    }
    public function addExpression($expr) { 
        $this->data[] = $expr;
    }
    
    
    /**
     * fields::addColumnpublic function()
     * 
     * @param mixed $functionName
     * CONCAT
     * MD5
     * @param mixed $fields
     * @return void
     */
    /*public function addColumnpublic function($functionName,$fields) {
        $a = func_get_args();
        $functionName = trim($functionName,'()');
        array_shift($a);
        $value = implode(',',$a);
        
        $this->data[] = "$functionName($value)";
    }*/
    public function addpublic function($functionName,$value) {
        $a = func_get_args();
        $functionName = trim($functionName,'()');
        array_shift($a);
        foreach($a as $k=>$v){
            if(is_null($v)){
                $a[$k] = 'NULL';
            }elseif($v=='DEFAULT'){
                $a[$k] = 'DEFAULT';
            }elseif($v instanceof fieldItem){
                $v->alias = '';
            }else{
                if(!is_numeric($v)) $v = DBX::escape($v);
                $a[$k] = "'$v'";
            }
        }
        $value = implode(',',$a);
         
        $this->data[] = "$functionName($value)";
    }
    public function raw() {
        return implode(', ', $this->data);
    }
    public function __toString() {
        $w = $this->raw();
        if($w)
            return "($w)";
        else
            return '';
    }
}
