<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;

class fields {
    private $data = array();
    
    public function addColumn($fieldName, $alias='') {
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
        $field->alias = $alias;  
        $this->data[] = $field;
    }
    public function addExpression($expr, $alias='') {
        $field = new fieldItem();
        $field->type = FIELD_EXP;
        $field->expr = $expr;
        $field->alias = $alias;  
        $this->data[] = $field;
    }
    public function addAll($tablename='') {
        $field = new fieldItem();
        $field->type = FIELD_EXP;
        if($tablename){
            if(is_scalar($tablename)){
                $field->expr = "`{$tablename}`.*";
            }elseif( $tablename instanceof tableItem){
                if($tablename->alias)
                    $field->expr =& $tablename->alias ;
                else
                    $field->expr =& $tablename->tablename ;
            }
        }
        else
            $field->expr = '*';
        $field->alias = '';  
        $this->data[] = $field;
    }
    /**
     * fields::addColumnpublic function()
     * 
     * @param mixed $functionName
     * CONCAT
     * MD5
     * @param mixed $alias
     * @param mixed $fields
     * @return void
     */
    public function addColumnpublic function($functionName, $alias, $fields) {
        $a = func_get_args();
        $functionName = trim($functionName,'()');
        array_shift($a);
        $alias = trim($alias,'`');
        array_shift($a);
        $expr = implode(',',$a);
        
        $field = new fieldItem();
        $field->type = FIELD_EXP;
        $field->expr = "$functionName($expr)";
        $field->alias = $alias;  
        $this->data[] = $field;
    }
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function __construct() {
    }
    public function raw($type=FIELD_COL) {
        if($this->type == FIELD_COL){
            $f=array();
            foreach($this->data as $field){
                if($field->raw(FIELD_COL))
                    $f[] = $field->raw(FIELD_COL);
            }
            return implode(', ', $f);
        }else{
            if(count($this->data))
                return implode(', ', $this->data);
            else
                return '';
        }
    }
    public function __toString() {
        $w = $this->raw();
        if($w)
            return "$w";
        else
            return '';
    }
}

class fieldItem{
    var $dbname ='';
    var $tablename ='';
    var $expr ='';
    var $alias ='';
    var $first = false;
    var $after ='';
    var $type =FIELD_COL;
    public function raw() {
        if($this->expr == '') return '';
        
        if(func_num_args())
            $type = func_get_arg(0);
        else
            $type =$this->type; 
        if($type == FIELD_COL){
            $a=array();
            if($this->dbname) $a[] = "`$this->dbname`";
            if($this->tablename) $a[] = "`$this->tablename`";
            $a[] = "`$this->expr`";
            $f = implode('.',$a);
            if($this->alias){
                return "$f AS $this->alias";
            }else{
                return $f;
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
