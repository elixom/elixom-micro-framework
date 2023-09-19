<?php
/**
 * @author Edwards
 * @copyright 2012
 */
include_once('const.php');

class EXSQL
{
    const VERSION = '1.0';
    
    private static function loadapi($name)
    {
        $f =__DIR__ . DIRECTORY_SEPARATOR . "{$name}.php";
        if(file_exists($f)) include_once($f);
    }
    public static function exists($name)
    {
        return file_exists(__DIR__ . DIRECTORY_SEPARATOR . "{$name}.php");
    }
    public static function __callStatic($name, $arguments) {
        $name =strtolower($name);
        self::loadapi($name);
        $name =strtoupper($name);
        $class = __CLASS__ . '\\' . $name;
        $reflect  = new ReflectionClass($class);
        $instance = $reflect->newInstanceArgs($arguments);
        return $instance;
    }
    
    
    public static function prepareQuery($sqlType = QUERY_SELECT){
        $sqlType=strtoupper($sqlType);
        if(in_array($sqlType,array(QUERY_SELECT,QUERY_UPDATE,QUERY_INSERT,QUERY_DELETE,QUERY_REPLACE))){
            $obj = self::prepareDml($sqlType);
        }elseif(in_array($sqlType,array(DML_ALTER,DML_DROP,DML_CREATE,DML_TRUNCATE))){
            $obj = self::prepareDdl($sqlType);
        }else{
            $obj = self::prepareDml(QUERY_SELECT);
        }
        return $obj;
    }
    public static function prepareDdl($type = QUERY_SELECT){
        self::loadapi('ddl');
        $obj = new EXSQL\ddl($type);
        return $obj;
    }
    public static function prepareDml($type = DML_ALTER){
        self::loadapi('dml');
        $obj = new EXSQL\dml($type);
        return $obj;
    }
    /*public static function clause(){
         
    }*/
    public static function delete()
    {
        return self::prepareDml(__FUNCTION__);
    }
    public static function update()
    {
        return self::prepareDml(__FUNCTION__);
    }
    public static function insert()
    {
        return self::prepareDml(__FUNCTION__);
    }
    public static function replace()
    {
        return self::prepareDml(__FUNCTION__);
    }
    public static function select()
    {
        return self::prepareDml(__FUNCTION__);
    }
    public static function alter()
    {
        return self::prepareDdl(__FUNCTION__);
    }
    public static function truncate()
    {
        return self::prepareDdl(__FUNCTION__);
    }
    public static function drop()
    {
        return self::prepareDdl(__FUNCTION__);
    }
    public static function create()
    {
        return self::prepareDdl(__FUNCTION__);
    }
}
