<?php
/**
 * @author Edwards
 * @copyright 2012
 * 
 * https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Query/QueryBuilder.php
 * 
 */
namespace EXSQL;

if(!defined('QUERY_SELECT'))     define('QUERY_SELECT','SELECT');
if(!defined('QUERY_UPDATE')) define('QUERY_UPDATE','UPDATE');
if(!defined('QUERY_INSERT'))     define('QUERY_INSERT','INSERT');
if(!defined('QUERY_DELETE'))     define('QUERY_DELETE','DELETE');
if(!defined('QUERY_REPLACE'))     define('QUERY_REPLACE','REPLACE');
//if(!defined('QUERY_SHOW'))     define('QUERY_SHOW','SHOW');
//if(!defined('QUERY_DESCRIBE'))     define('QUERY_DESCRIBE','DESCRIBE');
//if(!defined('QUERY_EXPLAIN'))     define('QUERY_EXPLAIN','EXPLAIN');

//$s = new statement();
//$s->select('a')->from('ap')->where('a=1','a=2');

include_once('fields.php');


class dml {
    private $type = QUERY_SELECT;
    private $fields = null;
    private $groupby = null;
    private $orderby = null;
    private $having = null;
    private $where = null;
    private $limit = null;
    
    private $table = null;
    
    private $predicate = null;
    private $onduplicate = null;
    private $select = null;
    private $values = null;
    private $set = null;
    
    public function __toString() {
        return $this->sql();
    }
    public function __construct() {
        if(func_num_args())
            $this->type(func_get_arg(0));
    }
    public function type($sqlType=QUERY_SELECT) {
        if(func_num_args()){
            $sqlType=strtoupper($sqlType);
            if(in_array($sqlType,array(QUERY_SELECT,QUERY_UPDATE,QUERY_INSERT,QUERY_DELETE,QUERY_REPLACE)))
                $this->type = $sqlType;
            return $this;
        }else{
            return $this->type;
        }
        
    }
    
    
    

    public function predicate($predicate) {
        $this->predicate = strtoupper($predicate);
        return $this;
    }
    public function leftJoin($table, $alias, $condition = null) {
        $this->table()->join(JOIN_LEFT,$table, $alias, $condition );
        return $this;
    }
    public function rightJoin($table, $alias, $condition = null) {
        $this->table()->join(JOIN_RIGHT,$table, $alias, $condition );
        return $this;
    }
    public function innerJoin($table, $alias, $condition = null) {
        $this->table()->join(JOIN_INNER,$table, $alias, $condition );
        return $this;
    }
    public function table() {
        if(!($this->table instanceof tableref)) $this->table = new tableref;
        if(func_num_args()){
            if(func_num_args()==1){
                $this->from(func_get_arg(0));
            }else{
                $this->from(func_get_arg(0),func_get_arg(1));
            }
            return $this;
        }else{
            return $this->table;
        }
    
    }
    /**
     * Create and add a query root corresponding to the table identified by the
     * given alias, forming a cartesian product with any existing query roots.
     *
     * <code>
     *     $qb = $conn->createQueryBuilder()
     *         ->select('u.id')
     *         ->from('users', 'u')
     * </code>
     *
     * @param string $from   The table
     * @param string $alias  The alias of the table
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function from($from='', $alias='')
    {
        if(!($this->table instanceof tableref)) $this->table = new tableref;
        if($from instanceof self)
            $this->table->select($alias, $from);
        else
            $this->table->table($from, $alias);
        return $this;
    }
    public function onduplicate($key, $value) {
        if(!($this->onduplicate instanceof onduplicate)) $this->onduplicate = new onduplicate;
        if(func_num_args()==1)
            $this->onduplicate->add(func_get_arg(0));
        elseif(func_num_args()==2)
            $this->onduplicate->add(func_get_arg(0),func_get_arg(1));
        elseif(func_num_args()==0)
            return $this->onduplicate;
        return $this;
    }
    public function values() {
        if(!($this->values instanceof values)) $this->values = new values;
        if(func_num_args()==1)
            $this->values->add(func_get_arg(0));
        elseif(func_num_args())
            $this->values->add(func_get_args());
        elseif(func_num_args()==0)
            return $this->values;
        return $this;
    }
    public function set($key, $value) {
        if(!($this->set instanceof set)) $this->set = new set;
        if(func_num_args()==1)
            $this->set->add(func_get_arg(0));
        elseif(func_num_args()==2)
            $this->set->add(func_get_arg(0),func_get_arg(1));
        elseif(func_num_args()==0)
            return $this->set;
        return $this;
    }
    /**
     * Turns the query being built into a bulk update query that ranges over
     * a certain table
     *
     * <code>
     *     $qb = statement()
     *         ->update('users', 'u')
     *         ->set('u.password', md5('password'))
     *         ->where('u.id = ?');
     * </code>
     *
     * @param string $update The table whose rows are subject to the update.
     * @param string $alias The table alias used in the constructed query.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function update($table = null, $alias = null)
    {
        $this->type('UPDATE');

        if(!empty($table)){
            if(!($this->table instanceof tableref)) $this->table = new tableref;
            if($from instanceof self)
                $this->table->select($alias, $table);
            else
                $this->table->table($table, $alias);
        }
        return $this;
    }
    /**
     * Turns the query being built into a bulk delete query that ranges over
     * a certain table.
     *
     * <code>
     *     $qb = statement()
     *         ->delete('users', 'u')
     *         ->where('u.id = :user_id');
     *         ->setParameter(':user_id', 1);
     * </code>
     *
     * @param string $delete The table whose rows are subject to the deletion.
     * @param string $alias The table alias used in the constructed query.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function delete($from='', $alias='')
    {
        $this->type('DELETE');
        if(!empty($from)){
            if(!($this->table instanceof tableref)) $this->table = new tableref;
            if($from instanceof self)
                $this->table->select($alias, $from);
            else
                $this->table->table($from, $alias);
        }
        return $this;
    }
    /**
     * Specifies fields  to be returned in the query result.
     * Replaces any previously specified selections, if any.
     *
     * <code>
     *     $qb = statement()
     *         ->select('u.id', 'p.id')
     *         ->from('users', 'u')
     *         ->leftJoin('u', 'phonenumbers', 'p', 'u.id = p.user_id');
     * </code>
     *
     * @param mixed $select The selection expressions.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function select() {
        if($this->type == 'INSERT' || $this->type =='REPLACE'){
            $c = __CLASS__;
            $this->select = new $c('SELECT');
            call_user_func_array(array($this->select,'select'),func_get_args());
            return $this->select;
        }else{
            $this->type('SELECT');
            if(func_num_args()==1){
                $this->fields = new fields(func_get_arg(0));
            }elseif(func_num_args()){
                $this->fields = new fields;
                foreach(func_get_args() as $f)
                    $this->fields->addColumn($f);
            }else{
                if(!($this->fields instanceof fields)) $this->fields = new fields;
            }
        }
        return $this;
    }
    public function fields() {
        if(!($this->fields instanceof fields)) $this->fields = new fields;
        if(func_num_args()){
            if(func_num_args()==1){
                $this->fields->addColumn(func_get_arg(0));
            }else{
                foreach(func_get_args() as $f)
                    $this->fields->addColumn($f);
            }
            return $this;
        }else{
            return $this->fields;
        }
    }
    public function limit($offset,$count=0) {
        if(!($this->limit instanceof limit)) $this->limit = new limit;
        if(func_num_args()==1){
            $this->limit->items($offset);
            return $this;
        }elseif(func_num_args()){
            $this->limit->offset($offset);
            $this->limit->items($count);
            return $this;
        }
        return $this->limit;
    }
    public function having() {
        if(func_num_args()){
            $this->having = new having;
            foreach(func_get_args() as $f)
                $this->having->addExpression($f)->type('AND');
            return $this;   
        }
        return $this->having;
    }
    public function where() {
        if(func_num_args()){
            $this->where = new where;
            foreach(func_get_args() as $f)
                $this->where->addExpression($f)->type('AND');
            return $this;   
        }
        return $this->where;
    }
    
    public function groupby() {
        
        if(is_null($this->groupby)) $this->groupby = new group();
        if($na = func_num_args()){
            if($na==1){
                $a = func_get_arg(0);
                if(is_array($a)){
                    foreach($a as $k=>$v){
                        if(order::isOrder($v))
                            $this->groupby->by($k);
                        else
                            $this->groupby->by($v);
                    }
                }else{
                    $a = explode(',',$a);
                    foreach($a as $v){
                        list($k,$v) = explode(' ',$v,3);
                        if(order::isOrder($v))
                            $this->orderby->by($k,$v);
                        else
                            $this->orderby->by($v);    
                    }
                }
            }else{
                foreach(func_get_args() as $v){
                    list($k,$v) = explode(' ',$v,3);
                    if(order::isOrder($v))
                        $this->orderby->by($k,$v);
                    else
                        $this->orderby->by($v);    
                }
            }
            return $this;
        }
        return $this->groupby;
    }
    public function orderby() {
        
        if(is_null($this->orderby)) $this->orderby = new order();
        if($na = func_num_args()){
            if($na==1){
                $a = func_get_arg(0);
                if(is_array($a)){
                    foreach($a as $k=>$v){
                        if(order::isOrder($v))
                            $this->orderby->by($k,$v);
                        else
                            $this->orderby->by($v);
                    }
                }else{
                    $a = explode(',',$a);
                    foreach($a as $v){
                        list($k,$v) = explode(' ',$v,3);
                        if(order::isOrder($v))
                            $this->orderby->by($k,$v);
                        else
                            $this->orderby->by($v);    
                    }
                }
            }else{
                foreach(func_get_args() as $v){
                    list($k,$v) = explode(' ',$v,3);
                    if(order::isOrder($v))
                        $this->orderby->by($k,$v);
                    else
                        $this->orderby->by($v);    
                }
            }
            return $this;
        }
        return $this->orderby;
    }
    public function sql() {
        $parts =array();
        switch($this->type){
        case QUERY_SELECT:
            $parts[] = 'SELECT';
            if($this->predicate){
                    $this->predicate = strtoupper($this->predicate);
                    $this->predicate= str_replace('DISTINCTROW','DISTINCT',$this->predicate);
                    $x = explode(' ',strtoupper($this->predicate));
                    $x = array_unique($x);
                    if(in_array('ALL',$x)){
                        $x = array_diff($x,array('DISTINCTROW','DISTINCT'));
                    }
                    if(in_array('SQL_BIG_RESULT',$x)){
                        $x = array_diff($x,array('SQL_SMALL_RESULT'));
                    }
                    if(in_array('SQL_NO_CACHE',$x)){
                        $x = array_diff($x,array('SQL_CACHE'));
                    }
                    $p = array('HIGH_PRIORITY'
                    ,'STRAIGHT_JOIN','SQL_SMALL_RESULT','SQL_BIG_RESULT','SQL_BUFFER_RESULT'
                    ,'SQL_CACHE','SQL_NO_CACHE','SQL_CALC_FOUND_ROWS');
                    $x = array_diff($x,$p);
                    $parts[] =  implode(' ', $x);
                
            }
            
            if($f =$this->fields->raw())
                $parts[] = $f;
            else
                $parts[] = "*";
            $parts[] = 'FROM';
            $parts[] = "$this->table";
            if((NULL !==$this->where)) $parts[] = "$this->where";
            if((NULL !==$this->groupby)) $parts[] = "$this->groupby";
            if((NULL !==$this->having)) $parts[] = "$this->having";
            
            
            if((NULL !==$this->orderby)) $parts[] = "$this->orderby";
            if((NULL !==$this->limit)) $parts[] = "$this->limit";
        break;
        case QUERY_INSERT:
            $parts[] = 'INSERT';
            if($this->predicate){
                    $this->predicate = strtoupper($this->predicate);
                    $this->predicate= str_replace('DISTINCTROW','DISTINCT',$this->predicate);
                    $x = explode(' ',strtoupper($this->predicate));
                    $x = array_unique($x);
                    
                    if(in_array('HIGH_PRIORITY',$x)){
                        $p=array('LOW_PRIORITY');
                        if((NULL !==$this->select)) $p[]='DELAYED';
                        $x = array_diff($x,$p);
                    }
                    if(in_array('LOW_PRIORITY',$x) && (NULL !==$this->select)){
                        $x = array_diff($x,array('DELAYED'));
                    }
                    
                    $p = array('LOW_PRIORITY','HIGH_PRIORITY','IGNORE');
                    if((NULL !==$this->select)) $p[]='DELAYED';
                    $x = array_diff($p,$x);
                    $parts[] =  implode(' ', $x);
            }
            $parts[] = 'INTO';
            
            $parts[] = "$this->table";
            if((NULL !==$this->set)){
                $parts[] = "$this->set";
            }elseif((NULL !==$this->select)){
                if((NULL !==$this->fields)){
                    if($f =$this->fields->raw(FIELD_COL)) $parts[] = "({$f})";
                }
                $parts[] = "$this->select";
            }elseif((NULL !==$this->fields)){
                if($f =$this->fields->raw(FIELD_COL)) $parts[] = "({$f})";
                if((NULL !==$this->values)) $parts[] = "$this->values";
            }
            if((NULL !==$this->onduplicate)){
                $parts[] = "$this->onduplicate";
            }
            
        break;
        case QUERY_REPLACE:
            $parts[] = 'REPLACE';
            if(in_array($this->predicate,array('LOW_PRIORITY','DELAYED'))) $parts[] = $this->predicate;;
            $parts[] = 'INTO';
            $parts[] = "$this->table";
            $canLI = false;
            if((NULL !==$this->set)){
                $parts[] = "$this->set";
                if($this->set->type() != SET_ONE) $canLI = true;
            }elseif((NULL !==$this->select)){
                if((NULL !==$this->fields)){
                    if($f =$this->fields->raw(FIELD_COL)) $parts[] = "({$f})";
                }
                $parts[] = "$this->select";
            }elseif((NULL !==$this->fields)){
                if($f =$this->fields->raw(FIELD_COL)) $parts[] = "({$f})";
                if((NULL !==$this->values)) $parts[] = "$this->values";
            }
            
            
            
        break;
        case QUERY_UPDATE:
            $parts[] = 'UPDATE';
            if(in_array($this->predicate,array('LOW_PRIORITY','IGNORE'))) $parts[] = $this->predicate;;
            
            $parts[] = "$this->table";
            $canWH = true;
            if((NULL !==$this->set)){
                $parts[] = "$this->set";
            }
            if((NULL !==$this->where)) $parts[] = "$this->where";
            if($this->table->count() == 1)if((NULL !==$this->orderby)) $parts[] = "$this->orderby";
            if($this->table->count() == 1)if((NULL !==$this->limit)) $parts[] = "$this->limit";
            
        break;
        case QUERY_DELETE:
            $parts[] = 'DELETE';
            if(in_array($this->predicate,array('LOW_PRIORITY','QUICK','IGNORE'))) $parts[] = $this->predicate;;
            $parts[] = 'FROM';
            $parts[] = "$this->table";
            if((NULL !==$this->where)) $parts[] = "$this->where";
            if((NULL !==$this->orderby)) $parts[] = "$this->orderby";
            if((NULL !==$this->limit)) $parts[] = "$this->limit";
        break;
        }
        
        return implode(' ', $parts);
    }
}
