<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;
class query
{
    private $result = null;
    private $query = null;
    public function __construct($query,$parent=null) {
        $this->query = $query;
        if($parent)
            $this->result = $parent->execute($query);
        else
            $this->result = DBX::execute($query);
        if(!is_resource($this->result)) return false;
    }
    public function __destruct() {
        if(is_resource($this->result)) mysql_free_result($this->result);
    }

    public function __get($name) {
        if($name=='query') return $this->query;
        if(method_exists(__CLASS__,$name)) return $this->$name();
    }
    public function rows()
    {
        return $this->num_rows();
    }

    public function num_rows()
    {
        if(!is_resource($this->result)) return false;
        return mysql_num_rows($this->result);
    }
    public function row()
    {
        if(!is_resource($this->result)) return false;
        return mysql_fetch_assoc($this->result);
    }
    public function rowArray()
    {
        if(!is_resource($this->result)) return false;
        return mysql_fetch_array($this->result);
    }
    public function rowObject()
    {
        if(!is_resource($this->result)) return false;
        return mysql_fetch_object($this->result,'row');
    }
    public function rowArrayEx($default = array(), $ON_FALSE_RETURN_DEFAULT=true)
    {
        if(!is_resource($this->result)) return false;
        $row= mysql_fetch_assoc($this->result);
        if($row===false){
            if($ON_FALSE_RETURN_DEFAULT)
                return $default;
            else
                return FALSE;
            
        } 
        foreach($default as $k => $v)
            if(!isset($row[$k]))
                $row[$k] = $v;
        return $row;
    }



}
class row {
    protected $data = array();
    public function Read($name, $default=false) {
        $name = strtolower($name);
        if(isset($this->data[$name]))
            return $this->data[$name];
        else
            return $default;
    }
    public function Seek($name, $default='') {
        $name = strtolower($name);
        if(!isset($this->data[$name]))
            $this->data[$name] = $default;
        return $this->data[$name];
    }
    public function exists($name){
        $name = strtolower($name);
        return isset($this->data[$name]);
    }
    public function isEmpty($name)
    {
        $name = strtolower($name);
        return empty($this->data[$name]);
    }
    public function delete($name)
    {
        unset($this->data[$name]);
    }
    public function all(){
        return $this->data;
    }
    public function __construct($data=array()) {
        if(func_num_args())
            $this->data = array_change_key_case($data,CASE_LOWER);
    }
    public function __get($name) {
        $name = strtolower($name);
        if(isset($this->data[$name]))
            return $this->data[$name];
        else
            return '';
    }
    public function __set($name, $value) {
        $name = strtolower($name);
        if(is_null($value))
            unset($this->data[$name]);
        else
            $this->data[$name] = $value;
    }
    public function __unset($name) {
        $name = strtolower($name);
        unset($this->data[$name]);
    }
    public function __isset($name) {
        $name = strtolower($name);
        return isset($this->data[$name]);
    }
    public function __toString() {
        return print_r($this,1);
    }    
}

?>