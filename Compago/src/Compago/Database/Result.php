<?php

namespace Compago\Database;
use \Compago\Database\Core\Row;
use \Compago\Database\Core\FieldDef;

class Result extends \MySQLi_Result
{
    protected $db;
    protected $query = null;
    private $met = 0;
    
    public function current_field() {return $this->current_field ;}
    public function tell() {return $this->current_field;}
    public function field_count() {return $this->field_count;}
    public function lengths() {return $this->lengths;}
    public function num_rows() {return $this->num_rows;}
    public function affected() {return $this->affected;}
    public function query() {return $this->query;}
    
    public function __construct($p,$query=null) {
        parent::__construct($p);
        $this->query = $query;
        $this->db = $p;
    }
    public function store_result(){
        $this->met = MYSQLI_STORE_RESULT;
        return $this->db->store_result();
    }
    public function use_result(){
        $this->met = MYSQLI_USE_RESULT;
        return $this->db->use_result();
    }
    public function __destruct() {
        try{
            @$this->free();
        }Catch(\Exception $e){
            error_log("Catch free exception: {$e->getMessage()}");
        }
    }
    public function __get($name) {
        if($name=='query') return $this->query;
        //if(method_exists(__CLASS__,$name)) return $this->$name();
    }
    public function __toString() {
        //return __CLASS__ ." ($this->query)";
    }

    public function getSQLType(){
          return Database::getSQLType($this->query);  
    }
    public function row(){return $this->fetch_assoc();}
    public function rowObject(){return $this->fetch_object();}
    
    public function fetch_object($class_name = NULL, array $params = NULL)
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
        if (method_exists('mysqli_result', 'fetch_all')) # Compatibility layer with PHP < 5.3
            $res = parent::fetch_all($resulttype);
        else
            for ($res = array(); $tmp = $this->fetch_array($resulttype);) $res[] = $tmp;

        return $res;
    }
    public function fetch_field(){
        if($f = parent::fetch_field()){
            return new FieldDef((array)$f);
        }
        return $f;
    }
    public function fetch_field_direct($nr){
        if($f = parent::fetch_field_direct($nr)){
            return new FieldDef((array)$f);
        }
        return $f;
    }
    public function fetch_fields(){
        if($f = parent::fetch_fields()){
            foreach($f as $k=>$a){
                $f[$k] = new FieldDef((array)$a);
            }
        }
        return $f;
    }
}

