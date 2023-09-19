<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;
include_once('expr.php');
if(!defined('TABLE_NAMED'))     define('TABLE_NAMED',0);
if(!defined('TABLE_SQL')) define('TABLE_SQL',1);

if(!defined('JOIN')) define('JOIN','');
if(!defined('JOIN_COMMA')) define('JOIN_COMMA',',');
if(!defined('JOIN_INNER')) define('JOIN_INNER','INNER');
if(!defined('JOIN_CROSS')) define('JOIN_CROSS','CROSS');


if(!defined('JOIN_LEFT')) define('JOIN_LEFT','LEFT');
if(!defined('JOIN_RIGHT')) define('JOIN_RIGHT','RIGHT');

if(!defined('JOIN_LEFTOUTER')) define('JOIN_LEFTOUTER','LEFT OUTER');
if(!defined('JOIN_RIGHTOUTER')) define('JOIN_RIGHTOUTER','RIGHT OUTER');
if(!defined('JOIN_STRAIGHT_JOIN')) define('JOIN_STRAIGHT_JOIN','STRAIGHT_JOIN');
if(!defined('JOIN_NATURAL')) define('JOIN_NATURAL','NATURAL');
if(!defined('JOIN_OUTER')) define('JOIN_OUTER','OUTER');

if(!defined('JOIN_NATURALLEFT')) define('JOIN_NATURALLEFT','NATURAL LEFT');
if(!defined('JOIN_NATURALRIGHT')) define('JOIN_NATURALRIGHT','NATURAL RIGHT');


class tableRef {
    private $table = null;
    private $joins = array();
    public function table($tableName, $alias='') {
        $field = new tableItem();
        $field->type = TABLE_NAMED;
        $field->expr = trim($tableName,'`');
        $field->alias = $alias;  
        $this->table = $field;
        return $this->table;
    }
    public function select($alias,$selectQuery) {
        $field = new tableItem();
        $field->type = TABLE_SQL;
        $field->expr = $selectQuery;
        $field->alias = $alias;  
        $this->table = $field;
        return $this->table;
    }
    public function join($type, $tableName, $alias='',$condition='') {
        $field = new joinItem();
        $field->type($type);
        $field->expr = $tableName;
        $field->alias = $alias;
        $field->on($condition);  
        $this->joins[] = $field;
        return $field;
    }
    public function joinUsing($type, $tableName, $alias='',$condition='') {
        $field = new joinItem();
        $field->type($type);
        $field->expr = $tableName;
        $field->alias = $alias;
        $field->using($condition);  
        $this->joins[] = $field;
        return $field;
    }
    public function count(){
        return count($this->data);
    }
    public function __toString() {
        $w = $this->raw();
        if($w)
            return $w;
        else
            return '';
        
    }
}
class tableItem{
    private $expr ='';
    private $alias ='';
    private $tabletype =TABLE_NAMED;
    public function __get($name) {
        $name=strtolower($name);
        
        if($name =='alias')
            return $this->alias;
        elseif($name =='tablename'){
            if($this->tabletype == TABLE_NAMED)
                return $this->expr;
            else
                return $this->alias;
        }
            
    }

    public function table($tableName, $alias='') {
        $this->tabletype = TABLE_NAMED;
        $this->expr = trim($tableName,'`');
        $this->alias = trim($alias,'`'); 
    }
    public function select($alias,$selectQuery) {
        $this->tabletype = TABLE_SQL;
        $this->expr = $selectQuery;
        $this->alias = trim($alias,'`');
    }
    
    public function raw() {
        if($this->expr == '') return '';
        if(func_num_args())
            $type = func_get_arg(0);
        else
            $type =$this->tabletype; 
        if($type == TABLE_NAMED){
            if($this->alias){
                return "`$this->expr` AS `$this->alias`";
            }else{
                return "`$this->expr`";
            }
        }else{
            if($this->alias){
                return "($this->expr) AS `$this->alias`";
            }else{
                return "($this->expr)";
            }
        }
    }
    public function __toString(){
        return $this->raw();
    }
}
class joinItem extends tableItem{
    private $jointype =JOIN_LEFT;
    private $on = array();
    private $using = '';
    
    public function type($joinType=JOIN_LEFT){
        if(func_num_args()){
            $joinType = strtoupper($joinType);
            if(in_array($joinType,array('LEFT','RIGHT','INNER','CROSS','LEFT OUTER','STRAIGHT_JOIN'
            ,'NATURAL','RIGHT OUTER','OUTER',',','')))
                $this->jointype = $joinType;
        }
        return $this->jointype;
    }
    public function using(){
        $a = func_get_args();
        $this->using = implode(',',$a);
    }
    public function andOn($field='',$value=''){
        if(count($this->on)) $this->on[] = 'AND';
        $expr = new expr();
        $expr->left = $field;
        $expr->right = $value;
        $expr->operator = '=';
        $this->on[] = $expr;
    }
    public function orOn($field='',$value=''){
        if(count($this->on)) $this->on[] = 'OR';
        $expr = new expr();
        $expr->left = $field;
        $expr->right = $value;
        $expr->operator = '=';
        $this->on[] = $expr;
    }
    public function raw() {
        $table = parent::raw();
        if($table == '') return '';
        
        if($this->jointype == 'NONE'){
            return $table;
        }
        
        $parts = array();
        $parts[] = $this->jointype;
        if($this->jointype==','){
            $parts[] = $table;
        }else{
            $parts[] = 'JOIN';
            $parts[] = $table;
            if($this->using){
                $parts[] = "USING ($this->using)";
            }else{
                $on = implode(' ', $this->on);
                $parts[] = "ON ($on)";
            }
        }
        return implode(' ', $parts);
    }
    public function __toString(){
        return $this->raw();
    }
}
?>