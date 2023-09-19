<?php

namespace Compago\Database\SQL;
use Compago\Database\SQL\Column;

class ColumnCollection extends \Compago\Database\Core\ColumnCollection{
    public function toString(/*$type=null*/) {
        $type = (func_num_args())?func_get_arg(0):null;
        $ff=array();
        foreach($this->columns as $field){
            $ff[] = $field->toString($type);
        }
        return implode(', ', $ff);
    }
    function add($options =array()) {
        if (isset($options['attribute_type']) && $options['attribute_type']=='INDEX'){
            $field = new Index($options);
        } else {
            $field = new Column($options);
        }
        $this->columns[] = $field;
        return $field;
    }
    function addOption($text) {
        $field = new Column();
        $field->attribute_type = "OPTION";
        $field->value = $text;
        $this->columns[] = $field;
        return $field;
    }
    function addCheck($expr) {
        $field = new Column();
        $field->attribute_type = "CHECK";
        $field->expr = $expr;
        $this->columns[] = $field;
        return $field;
    }
    function addExpression($expr, $alias='') {
        $field = new Column();
        $field->attribute_type = 'EXPR';
        $field->expr = $expr;
        $field->alias = $alias;  
        $this->columns[] = $field;
    }
    /*function addAll($tablename='') {
        $field = new Column();
        $field->attribute_type = 'EXPR';
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
        $this->columns[] = $field;
    }
    
   function addColumn($fieldName, $alias='') {
        $field = new Column();
        $field->attribute_type = "COLUMN";
        if(is_array($fieldName)){
            $c = count($fieldName);
            if($c==1)
                $field->expr = trim(array_shift($fieldName),'`');
            elseif($c==2){
                $field->tablename = trim(array_shift($fieldName),'`');
                $field->expr = trim(array_shift($fieldName),'`');
            }elseif($c==3){
                $field->dbname = trim(array_shift($fieldName),'`');
                $field->tablename = trim(array_shift($fieldName),'`');
                $field->expr = trim(array_shift($fieldName),'`');
            }
        }else
            $field->expr = trim($fieldName,'`');
        $field->alias = $alias;  
        $this->columns[] = $field;
    }
    /**
     * fields::addColumnFunction()
     * 
     * @param mixed $FunctionName
     * CONCAT
     * MD5
     * @param mixed $alias
     * @param mixed $fields
     * @return void
     * /
    function addColumnFunction($FunctionName, $alias, $fields) {
        $a = func_get_args();
        $FunctionName = trim($FunctionName,'()');
        array_shift($a);
        $alias = trim($alias,'`');
        array_shift($a);
        $expr = implode(',',$a);
        
        $field = new Column();
        $field->attribute_type = 'EXPR';
        $field->expr = "$FunctionName($expr)";
        $field->alias = $alias;  
        $this->columns[] = $field;
    }*/
}