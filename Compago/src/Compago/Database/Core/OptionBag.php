<?php
/**
 * @author Edwards
 * @copyright 2020
 */
namespace Compago\Database\Core;

class OptionBag{
    /*table_options
----------------------------------    
     ENGINE [=] engine_name
  | AUTO_INCREMENT [=] value
  | AVG_ROW_LENGTH [=] value
  | [DEFAULT] CHARACTER SET [=] charset_name
  | CHECKSUM [=] {0 | 1}
  | [DEFAULT] COLLATE [=] collation_name
  | COMMENT [=] 'string'
  | COMPRESSION [=] {'ZLIB'|'LZ4'|'NONE'}
  | CONNECTION [=] 'connect_string'
  | DATA DIRECTORY [=] 'absolute path to directory'
  | DELAY_KEY_WRITE [=] {0 | 1}
  | ENCRYPTION [=] {'Y' | 'N'}
  | INDEX DIRECTORY [=] 'absolute path to directory'
  | INSERT_METHOD [=] { NO | FIRST | LAST }
  | KEY_BLOCK_SIZE [=] value
  | MAX_ROWS [=] value
  | MIN_ROWS [=] value
  | PACK_KEYS [=] {0 | 1 | DEFAULT}
  | PASSWORD [=] 'string'
  | ROW_FORMAT [=] {DEFAULT|DYNAMIC|FIXED|COMPRESSED|REDUNDANT|COMPACT}
  | STATS_AUTO_RECALC [=] {DEFAULT|0|1}
  | STATS_PERSISTENT [=] {DEFAULT|0|1}
  | STATS_SAMPLE_PAGES [=] value
  | TABLESPACE tablespace_name
  | UNION [=] (tbl_name[,tbl_name]...)*/
  
    protected $data = [];
    public function __construct($data =array()) {
        if(func_num_args()){
            $this->data = array_change_key_case($data,CASE_UPPER);
        }
    }
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function add($name,$value=true){
        $name = strtoupper($name);
        if ($name == 'LANGUAGE SQL'){
            $name = 'LANGUAGE';
            $value = 'SQL';
        }
        if ($name == 'NOT DETERMINISTIC'){
            $name = 'DETERMINISTIC';
            $value = false;
        }
        if($value === null){
            $this->delete($name);
        }else{
            $this->data[$name] = $value;
        }
        return $this;
    }
    public function delete($name){
        $name = strtoupper($name);
        unset($this->data[$name]);
        return $this;
    }
    public function get($name){
        $name = strtoupper($name);
        if(isset($this->data[$name])){
            return $this->data[$name];
        }
        return null;
    }
    public function toString($only=[]) {
        $f=array();
        $use_only = func_num_args();
        $only = array_change_key_case($only,CASE_UPPER);
        
        foreach($this->data as $name => $value){
            if ($use_only){
                if (!in_array($name,$only)){
                    continue;
                }
            }
            if(is_bool($value)){
                if($value){
                    $f[] = $name;
                }
                else{
                    if($name =='DETERMINISTIC') $f[] = 'NOT DETERMINISTIC';
                }
            }else{
                if(in_array($name,array('COMMENT','COMPRESSION'))){
                    $f[] = "$name = '$value'";
                }else{
                    $f[] = "$name $value";
                }
            }
        }
        return implode(' ', $f);
    }
    public function toArray($only=[]) {
        if (func_num_args()){
            $f = [];
            $only = array_change_key_case($only,CASE_UPPER);
            foreach($this->data as $name => $value){
                if (in_array($name,$only)){
                    $f[$name] = $value;
                }
            }
            return $f;
        }
        return $this->data;
    }
    public function anyOf($only=[]) {
        $only = array_change_key_case($only,CASE_UPPER);
        $default = null;
        foreach($only as $name){
            if (isset($this->data[$name])){
                if ($default === null){
                    $default = $name;
                }
                if(is_bool($this->data[$name]) && ($this->data[$name] == false)){
                    continue;
                }
                return $name;
            }
        }
        return $default;
    }
    
    public function __toString() {
        return $this->toString();
    }
       
}