<?php

namespace Compago\Database\Core;


/* TO IMPLEMENT a class to encaulate
the return value of 
-fetch_field
-fetch_field_direct
-fetch_fields 

and return this class along with mthods that can convert FLAG and TYPE to text


if ($meta->flags & 4) { 
     echo 'Unique key flag is set'; 
  } 
  
  
  
*/
function h_type2txt($type_id)
{
    static $types;

    if (!isset($types))
    {
        $types = array();
        $constants = get_defined_constants(true);
        foreach ($constants['mysqli'] as $c => $n) if (preg_match('/^MYSQLI_TYPE_(.*)/', $c, $m)) $types[$n] = $m[1];
    }

    return array_key_exists($type_id, $types)? $types[$type_id] : NULL;
}

function h_flags2txt($flags_num)
{
    static $flags;

    if (!isset($flags))
    {
        $flags = array();
        $constants = get_defined_constants(true);
        foreach ($constants['mysqli'] as $c => $n) if (preg_match('/MYSQLI_(.*)_FLAG$/', $c, $m)) if (!array_key_exists($n, $flags)) $flags[$n] = $m[1];
    }

    $result = array();
    foreach ($flags as $n => $t) if ($flags_num & $n) $result[] = $t;
    return implode(' ', $result);
}
class FieldDef{
    //field def
    private $data =array();
    public function __construct($data =array()) {
        if(func_num_args()){
            $this->data = array_change_key_case($data,CASE_LOWER);;
        }
    }
    public function __call($name, $arguments) {
        $name = strtolower($name);
        if(isset($this->data[$name])){
            return $this->data[$name];
        }
        return false;
    }
    public function __get($name) {
        if(method_exists($this,$name)){
            return $this->$name();
        }
        $name = strtolower($name);
        if(isset($this->data[$name])){
            return $this->data[$name];
        }
        return false;
    }
    public function toArray() {
        $a = $this->data;
        $a['flags_text'] = $this->flags_text();
        $a['type_text'] = $this->type_text();
        return $a;
    }
    public function flags_text(){
        return h_flags2txt($this->flags);
    }
    public function type_text(){
        return h_type2txt($this->type);
    }
    public function is_alias(){
        return $this->name != $this->orgname;
    }
    public function is_calculated(){
        return !$this->orgname && ($this->name != $this->orgname);
    }
    public function is_table_alias(){
        return $this->table != $this->orgtable;
    }
    public function is_primary_key(){
        return $this->has_flag(MYSQLI_PRI_KEY_FLAG);
    }
    public function has_flag($flag){
        return (($this->flags & $flag) == $flag);
    }
}
