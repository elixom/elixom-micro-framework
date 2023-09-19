<?php

namespace Compago\Database\SQL;
use Compago\Database\Core\PredicateBag;

class Dml extends \Compago\Database\SQL\ClauseCollection{
    protected $type = 'SELECT';
    protected $predicate = new PredicateBag;
    
    public function type($sqlType='SELECT') {
        if(func_num_args()){
            $sqlType=strtoupper($sqlType);
            if(in_array($sqlType,array('SELECT','UPDATE','INSERT','DELETE','REPLACE')))
                $this->type = $sqlType;
            return $this;
        }else{
            return $this->type;
        }
    }
    public function predicate($predicate) {
        $this->predicate->set($predicate);
        return $this;
    }
    
    
    
    
    
    
    
    
    
    
    
        
    
    protected $fields = null;
    protected $groupby = null;
    protected $orderby = null;
    protected $having = null;
    protected $where = null;
    protected $limit = null;
    protected $table = null;
    protected $onduplicate = null;
    protected $select = null;
    protected $values = null;
    protected $set = null;
    
    
    
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
    public function join($table, $alias) {
        $join = $this->table()->join($table, $alias,JOIN_COMMA, $condition );
        return $join;
    }
    public function leftJoin($table, $alias, $condition = null) {
        $this->table()->join($table, $alias,JOIN_LEFT, $condition );
        return $this;
    }
    public function rightJoin($table, $alias, $condition = null) {
        $this->table()->join($table, $alias,JOIN_RIGHT, $condition );
        return $this;
    }
    public function innerJoin($table, $alias, $condition = null) {
        $this->table()->join($table, $alias,JOIN_INNER, $condition );
        return $this;
    }
    
    public function values() {
        if(!($this->values instanceof values_clause)) $this->values = new values_clause;
        $n = func_num_args();
        if ($n == 0){
            return $this->values;
        }
        if($n==1){
            $this->values->add(func_get_arg(0));
        } else {
            $this->values->add(func_get_args());
        }
        return $this;
    }
    public function set() {
        if(!($this->set instanceof set_clause)) $this->set = new set_clause;
        
        $n = func_num_args();
        if ($n == 0){
            return $this->set;
        }
        $this->set = new set_clause;
        $this->set->addMultiple(func_get_args());
        return $this;
    }
    public function onduplicate() {
        if(!($this->onduplicate instanceof onduplicate_clause)) $this->onduplicate = new onduplicate_clause;
         $n = func_num_args();
        if ($n == 0){
            return $this->onduplicate;
        }
        $this->onduplicate = new onduplicate_clause;
        $this->onduplicate->addMultiple(func_get_args());
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
            $this->select = new self('SELECT');
            call_user_func_array(array($this->select,'select'),func_get_args());
            return $this->select;
        }else{
            $this->type('SELECT');
            if(func_num_args()){
                $this->fields = new dml_field_collection;
                foreach(func_get_args() as $f)
                    $this->fields->addColumn($f);
            }
        }
        return $this;
    }
    public function fields() {
        if(!($this->fields instanceof field_collection)) $this->fields = new dml_field_collection;
        if(func_num_args()){
            $this->fields = new dml_field_collection;
            if(func_num_args()){
                foreach(func_get_args() as $f)
                    $this->fields->addColumn($f);
            }
            return $this;
        }
        return $this->fields;
    }
    public function limit($offset,$count=0) {
        if(!($this->limit instanceof limit)) $this->limit = new limit;
        if(func_num_args()==1){
            if (is_array($offset)){
                $o = array_shift($offset);
                if (count($offset)){
                    $this->limit->offset($o);
                    $this->limit->items(array_shift($offset));
                } else {
                    $this->limit->offset($o);
                }
            }else{
                $this->limit->items($offset);
            }
            return $this;
        }elseif(func_num_args()){
            $this->limit->offset($offset);
            $this->limit->items($count);
            return $this;
        }
        return $this->limit;
    }
    public function having() {
        if(!($this->having instanceof having)) $this->having = new having('AND');
        if(func_num_args()){
            $this->having = new having('AND', func_get_args());
            return $this;   
        }
        return $this->having;
    }
    public function where() {
        if(!($this->where instanceof where)) $this->where = new where('AND');
        if(func_num_args()){
            $this->where = new where('AND', func_get_args());
            return $this; 
        }
        return $this->where;
    }
    public function orWhere($where)
    {
        $args = func_get_args();
        if($this->where instanceof where){
            if ( $where->getType() === CompositeExpression::TYPE_OR) {
                $where->addMultiple($args);
            } else {
                array_unshift($args, $this->where->toCompositeExpression());
                $this->where = new where(CompositeExpression::TYPE_OR, $args);
            }
        } else {
            $this->where = new where(CompositeExpression::TYPE_OR, $args);
        }
        return $this;
    }
    public function andWhere($where)
    {
        $args = func_get_args();
        if($this->where instanceof where){
            if ( $where->getType() === CompositeExpression::TYPE_AND) {
                $where->addMultiple($args);
            } else {
                array_unshift($args, $this->where->toCompositeExpression());
                $this->where = new where(CompositeExpression::TYPE_AND, $args);
            }
        } else {
            $this->where = new where(CompositeExpression::TYPE_AND, $args);
        }
        return $this;
    }
    
    public function orHaving($having)
    {
        $args = func_get_args();
        if($this->having instanceof having){
            if ( $having->getType() === CompositeExpression::TYPE_OR) {
                $having->addMultiple($args);
            } else {
                array_unshift($args, $this->having->toCompositeExpression());
                $this->having = new having(CompositeExpression::TYPE_OR, $args);
            }
        } else {
            $this->having = new having(CompositeExpression::TYPE_OR, $args);
        }
        return $this;
    }
    public function andHaving($having)
    {
        $args = func_get_args();
        if($this->having instanceof having){
            if ( $having->getType() === CompositeExpression::TYPE_AND) {
                $having->addMultiple($args);
            } else {
                array_unshift($args, $this->having->toCompositeExpression());
                $this->having = new having(CompositeExpression::TYPE_AND, $args);
            }
        } else {
            $this->having = new having(CompositeExpression::TYPE_AND, $args);
        }
        return $this;
    }
    
    public function groupby() {
        if(!($this->groupby instanceof group)) $this->groupby = new group();
        if($na = func_num_args()){
            $this->groupby = new group();
            if($na==1){
                $this->groupby->add(func_get_arg(0));
            }else{
                $this->groupby->add(func_get_args());
            }
            return $this;
        }
        return $this->groupby;
    }
    public function orderby() {
        if(!($this->orderby instanceof order)) $this->orderby = new order();
        if($na = func_num_args()){
            $this->orderby = new order();
            if($na==1){
                $this->orderby->add(func_get_arg(0));
            }else{
                $this->orderby->add(func_get_args());
            }
            return $this;
        }
        return $this->orderby;
    }
    public function toString() {
        $parts =array();
        switch($this->type){
        case QUERY_SELECT:
            $parts[] = 'SELECT';
            if($f = $this->forSelectPredicate()){
                $parts[] = $f;
            }
            if((NULL !==$this->fields) && ($f =$this->fields->toString())){
                $parts[] = $f;
            } else{
                $parts[] = "*";
            }
            if(NULL !==$this->table){
                $parts[] = "FROM $this->table";
            }
            
            if((NULL !==$this->where)) $parts[] = "$this->where";
            if((NULL !==$this->groupby)) $parts[] = "$this->groupby";
            if((NULL !==$this->having)) $parts[] = "$this->having";
            if((NULL !==$this->orderby)) $parts[] = "$this->orderby";
            if((NULL !==$this->limit)) $parts[] = "$this->limit";
        break;
        case QUERY_INSERT:
            $parts[] = 'INSERT';
            if($f = $this->forInsertPredicate(NULL !==$this->select)){
                $parts[] = $f;
            }
            if(NULL !==$this->table){
                $parts[] = "INTO $this->table";
            }
            if((NULL !==$this->set)){
                $parts[] = "$this->set";
            }elseif((NULL !==$this->select)){
                if((NULL !==$this->fields)){
                    if($f =$this->fields->toString(FIELD_COL)) $parts[] = "({$f})";
                }
                $parts[] = "$this->select";
            }elseif((NULL !==$this->fields)){
                if($f =$this->fields->toString(FIELD_COL)) $parts[] = "({$f})";
                if((NULL !==$this->values)) $parts[] = "$this->values";
            }elseif((NULL !==$this->values)){
                 $parts[] = "$this->values";
            }
            if((NULL !==$this->onduplicate)){
                $parts[] = "$this->onduplicate";
            }
            
        break;
        case QUERY_REPLACE:
            $parts[] = 'REPLACE';
            if($f = $this->forReplacePredicate()){
                $parts[] = $f;
            }
            if(NULL !==$this->table){
                $parts[] = "INTO $this->table";
            }
            if((NULL !==$this->set)){
                $parts[] = "$this->set";
            }elseif((NULL !==$this->select)){
                if((NULL !==$this->fields)){
                    if($f =$this->fields->toString(FIELD_COL)) $parts[] = "({$f})";
                }
                $parts[] = "$this->select";
            }elseif((NULL !==$this->fields)){
                if($f =$this->fields->toString(FIELD_COL)) $parts[] = "({$f})";
                if((NULL !==$this->values)) $parts[] = "$this->values";
            }elseif((NULL !==$this->values)){
                 $parts[] = "$this->values";
            }
            
            
            
        break;
        case QUERY_UPDATE:
            $parts[] = 'UPDATE';
            if($f = $this->forUpdatePredicate()){
                $parts[] = $f;
            }
            if(NULL !==$this->table){
                $parts[] = "$this->table";
            }
            if((NULL !==$this->set)){
                $parts[] = "$this->set";
            }
            if((NULL !==$this->where)) $parts[] = "$this->where";
            if($this->table->count() == 1){
                if((NULL !==$this->orderby)) $parts[] = "$this->orderby";
                if((NULL !==$this->limit)){
                    $limitClone = clone $this->limit;
                    if ($limitClone->offset()){
                        if ($limitClone->items()){
                            $limitClone->offset(null);
                        }
                    }
                    
                    $parts[] = "$limitClone";
                }
            }
            
        break;
        case QUERY_DELETE:
            $parts[] = 'DELETE';
            if($f = $this->forDeletePredicate()){
                $parts[] = $f;
            }
            if((NULL !==$this->fields) && ($f =$this->fields->toString())){
                $parts[] = $f;
            }
            if(NULL !==$this->table){
                $parts[] = "FROM $this->table";
            }
            if((NULL !==$this->where)) $parts[] = "$this->where";
            if($this->table->count() == 1){
                if((NULL !==$this->orderby)) $parts[] = "$this->orderby";
                if((NULL !==$this->limit)){
                    $limitClone = clone $this->limit;
                    if ($limitClone->offset()){
                        if ($limitClone->items()){
                            $limitClone->offset(null);
                        }
                    }
                    
                    $parts[] = "$limitClone";
                }
            }
        break;
        }
        return implode(' ', $parts);
    }
    
    private function forSelectPredicate(){
        $parts = [];
        $currentPredicates = $this->predicate->toArray();
        $eliminatePredicates = array();
        if(in_array('DISTINCTROW',$currentPredicates)){
            $currentPredicates[] = 'DISTINCT';
        }
        if(in_array('ALL',$currentPredicates)){
            $eliminatePredicates[] = 'DISTINCTROW';
            $eliminatePredicates[] = 'DISTINCT';
        }
        if(in_array('SQL_BIG_RESULT',$currentPredicates)){
            $eliminatePredicates[] = 'SQL_SMALL_RESULT';
        }
        if(in_array('SQL_CACHE',$currentPredicates)){
            $eliminatePredicates[] = 'SQL_NO_CACHE';
        }
        $currentPredicates = array_diff($currentPredicates,$eliminatePredicates);
        $predicate1 = array_intersect($currentPredicates,['ALL','DISTINCT']);
        $parts[] =  implode(' ', $predicate1);
        $predicate1 = array_intersect($currentPredicates,['HIGH_PRIORITY','STRAIGHT_JOIN']);
        $parts[] =  implode(' ', $predicate1);
        $predicate1 = array_intersect($currentPredicates,['SQL_SMALL_RESULT','SQL_BIG_RESULT','SQL_BUFFER_RESULT']);
        $parts[] =  implode(' ', $predicate1);
        $predicate1 = array_intersect($currentPredicates,['SQL_NO_CACHE','SQL_CALC_FOUND_ROWS','SQL_CACHE']);
        $parts[] =  implode(' ', $predicate1);
        return implode(' ',$parts);
    }
    private function forInsertPredicate($disallow_delayed=false){
        $parts = [];
        $currentPredicates = $this->predicate->toArray();
        $eliminatePredicates = array();
        if(in_array('HIGH_PRIORITY',$currentPredicates)){
            $eliminatePredicates[] = 'LOW_PRIORITY';
        }elseif(in_array('LOW_PRIORITY',$currentPredicates)){
            $eliminatePredicates[] = 'HIGH_PRIORITY';
        }
        if($disallow_delayed){
            $eliminatePredicates[]='DELAYED';
        }
        $currentPredicates = array_diff($currentPredicates,$eliminatePredicates);
        
        $acceptablePredicates = array('LOW_PRIORITY','HIGH_PRIORITY');
        if(!$disallow_delayed) $acceptablePredicates[]='DELAYED';
        $predicate1 = array_intersect($currentPredicates,$acceptablePredicates);
        $parts[] =  implode(' ', $predicate1);
        $predicate1 = array_intersect($currentPredicates,['IGNORE']);
        $parts[] =  implode(' ', $predicate1);
        return implode(' ',$parts);
    }
    private function forReplacePredicate(){
        $parts = [];
        $currentPredicates = $this->predicate->toArray();
        $eliminatePredicates = array();
        if(in_array('DELAYED',$currentPredicates)){
            $eliminatePredicates[] = 'LOW_PRIORITY';
        }
        if(in_array('LOW_PRIORITY',$currentPredicates)){
            $eliminatePredicates[] = 'DELAYED';
        }
        $currentPredicates = array_diff($currentPredicates,$eliminatePredicates);
        
        $acceptablePredicates = array('LOW_PRIORITY','DELAYED');
        $predicate1 = array_intersect($currentPredicates,$acceptablePredicates);
        $parts[] =  implode(' ', $predicate1);
        return implode(' ',$parts);
    }
    private function forUpdatePredicate(){
        $parts = [];
        $currentPredicates = $this->predicate->toArray();
        $predicate1 = array_intersect($currentPredicates,['LOW_PRIORITY']);
        $parts[] =  implode(' ', $predicate1);
        $predicate1 = array_intersect($currentPredicates,['IGNORE']);
        $parts[] =  implode(' ', $predicate1);
        return implode(' ',$parts);
    }
    private function forDeletePredicate(){
        $parts = [];
        $currentPredicates = $this->predicate->toArray();
        $predicate1 = array_intersect($currentPredicates,['LOW_PRIORITY']);
        $parts[] =  implode(' ', $predicate1);
        $predicate1 = array_intersect($currentPredicates,['QUICK']);
        $parts[] =  implode(' ', $predicate1);
        $predicate1 = array_intersect($currentPredicates,['IGNORE']);
        $parts[] =  implode(' ', $predicate1);
        return implode(' ',$parts);
    }
}