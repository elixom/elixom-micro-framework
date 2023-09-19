<?php

namespace Compago\Database;

use \Compago\Database\Core\Row;

class MultiResult {
    protected $db;
    protected $query = null;
    protected $rs = null;
    private $met = 0;
    public function __construct($p,$query=null,$iState=false) {
        $this->query = $query;
        $this->db = $p;
        if($iState){
            $this->store_result();
        }
    }
    public function __destruct() {
        $this->free();
    }
    public function __call($name, $arguments) {
        $name = strtolower($name);
        if($name=='query') return $this->query;
        
        if(in_array($name,array('current_field','field_count','lengths','num_rows'))){
            if($this->rs === null) $this->store_result();
            if($this->rs){
                return $this->rs->$name;
            }
        }
        
        if($name=='affected') $name='affected_rows';
        if(in_array($name,array('affected_rows','sqlstate','errno','error','warning_count','insert_id'))){
            if($this->rs === null) $this->store_result();
            if($this->rs){
                return $this->db->$name;
            }
        }
        
        if(in_array($name,array('data_seek','fetch_array','fetch_assoc','fetch_field_direct',
                'fetch_field','fetch_fields','fetch_row','field_seek'))){
            if($this->rs === null) $this->store_result();
            if($this->rs){
                return call_user_func_array(array($this->rs,$name), $arguments) ;
            }
        }
    }
    public function __get($name) {
        $name = strtolower($name);
        if($name=='query') return $this->query;
        
        if(in_array($name,array('current_field','field_count','lengths','num_rows'))){
            if($this->rs === null) $this->store_result();
            if($this->rs){
                return $this->rs->$name;
            }
        }
        if($name=='affected') $name='affected_rows';
        if(in_array($name,array('affected_rows','sqlstate','errno','error','warning_count','insert_id'))){
            if($this->rs === null) $this->store_result();
            if($this->rs){
                return $this->db->$name;
            }
        }
        if($name=='more_results' || $name=='more') return $this->more_results();
        return false;
    }
    public function store_result(){
        $this->rs = $this->db->store_result();
        $this->met = MYSQLI_STORE_RESULT;
        return $this->rs;
    }
    public function use_result(){
        $this->rs = $this->db->use_result();
        $this->met = MYSQLI_USE_RESULT;
        return $this->rs;
    }
    public function more_results(){
        return $this->db->more_results();
    }
    public function next_result(){
        if(!$this->more_results()){
            return false;
        }
        $r = $this->db->next_result();
        if($this->met == MYSQLI_USE_RESULT){
            $this->use_result();
        }else{
            $this->store_result();
        }
        return $r;
    }
    public function free() {
        while ($this->more_results() && $this->next_result());
        if($this->rs){
            $this->rs->free();
            $this->rs = false;
        }
    }
    public function close() { $this->free();}
    
    public function getSQLType(){
          return Database::getSQLType($this->query);  
    }
    
    public function row(){return $this->fetch_assoc(); }
    public function rowObject(){return $this->fetch_object();}
    
    public function fetch_object()
    {
        $row = $this->fetch_assoc();
        if($row){
            return new Row($row);
        }else{
            return $row;
        }
    }
    public function fetch($asObject=false)
    {
        if($asObject)
            return $this->fetch_object();
        else
            return $this->fetch_assoc();
    }

    public function fetch_all($resulttype = MYSQLI_NUM)
    {
        for ($res = array(); $tmp = $this->fetch_array($resulttype);) $res[] = $tmp;
        return $res;
    }
}

