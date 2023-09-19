<?php

namespace Compago\Database;
class Database
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public static function connect($hostname, $username, $password, $database,$port=null,$socket= null, $flags=null){
        $db = new DatabaseInstance;
        $db->real_connect($hostname, $username, $password, $database,$port,$socket,$flags);
        if(!$db->errno()){
            $db->set_charset('utf8');
        }
        return $db;
    }
    public static function create(){
        return new DatabaseInstance(); 
    }
    public static function openSchemaDefinition($filepath=null) {
        if($filepath && file_exists($filepath)){
            $str = file_get_contents($filepath);
            return new Blueprint\Table($str);
        }else{
            return new Blueprint\Table();
        }
    }
    public static function selectStatement($fields,$table, $where='',$order='',$limit=0,$groupBy=''){
        return new Sql\SelectStatement($fields,$table, $where,$order,$limit,$groupBy);
    }
    public static function fieldset(){
        $w =  new FieldClause();
        if(func_num_args()){
            $a = func_get_args();
            foreach($a as $f) $w->add($f);
        }
        return $w;
    }
    public static function where($type = 'AND'){
        $w =  new WhereClause();
        $w->set_type($type);
        return $w;
    }
    public static function getSQLType($str){
      $str=trim($str);
      return strtoupper(substr($str,0,strpos($str,' ')));      
    }
}
