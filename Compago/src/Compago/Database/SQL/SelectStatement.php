<?php

namespace Compago\Database\SQL;

class SelectStatement {
// SELECT [ALL | DISTINCT] column1[,column2] 
//FROM table1[,table2] [WHERE "conditions"] 
//[GROUP BY "column-list"] [HAVING "conditions] [ORDER BY "column-list" [ASC | DESC] ]
    protected $distinct=false, $fields='*',$table='DUAL', $where='',$having='',$order='',$limit=0,$groupby='';
     
    public function __construct($fields,$table, $where='',$order='',$limit=0,$groupby='') {
        $this->fields = $fields;
        $this->table = $table;
        if($where) $this->where = $where;
        if($order) $this->order = $order;
        if($limit) $this->limit = $limit;
        if($groupby) $this->groupby = $groupby;
    }
    public function __get($name) {
        $name = strtolower($name);
        if($name == 'orderby') $name = 'order';
        if($name == 'group') $name = 'groupby';
        if($name == 'column') $name = 'fields';
        
        if(in_array($name,array('distinct','fields','table','where','having','order','limit','groupby'))){
            return $this->$name;
        }
        if($name == 'offset'){
            return $this->offset();
        }
    }
    public function __set($name, $value) {
        $name = strtolower($name);
        if($name == 'orderby') $name = 'order';
        if($name == 'group') $name = 'groupby';
        if($name == 'column') $name = 'fields';
        
        if(in_array($name,array('distinct','fields','table','where','having','order','limit','groupby'))){
            $this->$name = $value;
        }
        if($name == 'offset'){
            $this->offset($value);
        }
    }

    public function __call($name, $arguments) {
        $name = strtolower($name);
        if($name == 'orderby') $name = 'order';
        if($name == 'group') $name = 'groupby';
        if($name == 'column') $name = 'fields';
        
        if(in_array($name,array('distinct','fields','table','where','having','order','limit','groupby'))){
            $n = count($arguments);
            if($n==0){
                return $this->$name;
            }else if($n ==1){
                $this->$name = $arguments[0] ;
            }else{
                $this->$name = $arguments;
            }
            return $this;
        }
    }
    public function limit($offset=null,$limit=null) {
        $n = func_num_args();
        if($n == 0){
            return $this->limit;
        }
        if($n==1){
            if(is_array($this->limit)){
                $this->limit[1] = (int)$offset;
            }else{
                $this->limit = $offset;
            }
        }else{
            if(!is_array($this->limit)){
                if($limit === null){
                    $limit = $this->limit;
                }
                $this->limit = array();
            }
            $this->limit[0] = (int)$offset;
            if($limit)$this->limit[1] = $limit;
        }
        if(isset($this->limit[1]) && $this->limit[1]==0){
            unset($this->limit[1]);
        }
        return $this;
    }
    public function offset($offset=null) {
        $n = func_num_args();
        if($n == 0){
            if(is_array($this->limit)){
                return $this->limit[0];
            }elseif($this->limit){
                return 0;
            }
            return null;
        }
        $this->limit($offset);
        return $this;
    }
    public function addWhere($w) {
        if(!is_array($this->where)){
            $ww = $this->where;
            if($ww){
                $this->where =array($ww);
            }else{
                $this->where =array();
            }
        }
        $aa =array();
        if(func_num_args() > 1){
            $aa = array_filter(func_get_args());
        }else if($w){
            if(is_array($w)){
                $aa = array_filter($w);
            }else{
                $aa[] =$w;
            }
        }
        foreach($aa as $w){
            $this->where[] = $w;
        }
    }
    public function __toString() {
        return $this->toString();
    }
    public function toString(){
        $table = $this->table;
        $fields = $this->fields;
        $where = $this->where;
        $order = $this->order;
        $limit = $this->limit;
        $groupby = $this->groupby;
        $having = $this->having;
        
        
        if(is_array($table)) $table= implode(', ',array_filter($table));
        if(is_array($fields))$fields= implode(',',array_filter($fields));
        if(is_array($order))$order= implode(',',array_filter($order));
        if(is_array($limit))$limit= implode(',',$limit);
        if(is_array($where)) $where= implode(' AND ',array_filter($where));
        if(is_array($having)) $having= implode(' AND ',array_filter($having));
        if(is_array($groupby)) $groupby= implode(',',array_filter($groupby));
        
        
        
        if(!$fields) $fields='*';
        
        $query = "SELECT";
        if($this->distinct) $query .= ' DISTINCT';
        $query .= " $fields FROM $table";
        if($where)  $query.=" WHERE $where";
        if($groupby)  $query.=" GROUP BY $groupby";
        if($having)  $query.=" HAVING $having";
        if($order)  $query.=" ORDER BY $order";
        if($limit)  $query.=" LIMIT $limit";
        return $query;
    }
    
}