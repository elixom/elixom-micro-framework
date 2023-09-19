<?php
/**
 * @author Edwards
 * @copyright 2020
 * 
 * All strucural defines should derive from this class.
 * - Tabe Schema
 * - SQL DML (Alter and Create)
 * 
 * Data Elements(fields) should not derive from this class
 */
namespace Compago\Database\Core;

abstract class Attribute{
    protected $options = array();
    abstract function toString();
    
    public function toArray(){
        return $this->options;
    }
    public function __construct($data =null){
        if(is_array($data)){
            $this->setIndexOptions($data);
            $this->setColumnOptions($data);
        }
    }
    public function __call($name,$args){
        $n = count($args);
        $name = strtolower($name);
        if (in_array($name,['after','autoincrement','charset','collation','comment',
                            'default','first','unsigned','index_type','using',
                            'primary','unique','visible'])){
            if ($n == 0) {
                $this->__set($name, true);
            } else {
                $this->__set($name,$args[0]);
            }
            return $this;
        }
    }
    public function __toString() {
        return $this->toString();
    }
    public function __get($name) {
        $name = strtolower($name);
        if($name =='expr'){
            $name = 'generation_expression';
        }
        if($name =='field'){
            $name = 'name';
        }
        if($name =='columns'){
            $name = 'fields';
        }
        if($name =='autoincrement'){
            $name = 'auto_increment';
        }
        if(in_array($name,array('withparser','with_parser'))){
            $name = 'parser';
        }
        if(in_array($name,array('foreign','fk','foreignkey','foreign_key'))){
            $name = 'foreign';
        }
        if(in_array($name,array('index_type','indextype','using'))){
            $name = 'index_type';
        }
        if($name =='is_visible' || $name =='visible'){
            $name ='is_visible';
        }
        if(isset($this->options[$name])){
            return $this->options[$name];
        }
        return null;
    }
    public function __set($name, $value) {
        $name =strtolower($name);
        if(in_array($name,array('datatype','data_type','data_type_definition','column_type'))){
            $this->setType($value);
            return;
        }
        if(in_array($name,array('foreign','fk','foreignkey','foreign_key'))){
            if($value){
                if(is_bool($value)){
                    $this->options['foreign'] = $value;
                }else{
                    $this->options['name'] = $value;
                    $this->options['foreign'] = true;
                }
            }else{
                $this->options['foreign'] = false;
            }
            return;
        }
        if($name =='not_null'){
            $name ='is_nullable';
            if(strtoupper($value)=='NO'){
                $value = false;
            }elseif(strtoupper($value)=='YES'){
                $value = true;
            }
            $value = !$value;
        }
        if($name =='null'){
            $name ='is_nullable';
            if(strtoupper($value)=='NO'){
                $value = false;
            }elseif(strtoupper($value)=='YES'){
                $value = true;
            }else{
                //$value = !!$value;
            }
        }
        if($name =='is_visible' || $name =='visible'){
            $name ='is_visible';
            if(strtoupper($value)=='NO'){
                $value = false;
            }elseif(strtoupper($value)=='YES'){
                $value = true;
            }elseif ($value !== null){
                $value = !!$value;
            }
        }
        
        if(in_array($name,array('withparser','with_parser'))){
            $name = 'parser';
        }
        if($name =='is_nullable'){
            $value = (bool)$value;
        }
        if($name =='expr'){
            $name = 'generation_expression';
        }
        if($name =='autoincrement'){
            $name = 'auto_increment';
        }
        if($name =='field'){
            $name = 'name';
        }
        if($name =='columns'){
            $name = 'fields';
        }
        if(in_array($name,array('index_type','indextype','using'))){
            $name = 'index_type';
            $value = strtoupper($value);
        }
        $this->options[$name] = $value;
    }
    public function toDefinitionArray(){
        $a = array();
        $a['data_type_definition'] = $this->getDataType();
        $a['data_type'] = $this->data_type;
        if($this->length){
            $a['length'] = $this->length;
        }
        if($this->precision){
            $a['precision'] = $this->precision;
        }
        if($this->scale){
            $a['scale'] = $this->scale;
        }
        if($this->unsigned){
            $a['unsigned'] = $this->unsigned;
        }
        if($this->zerofill){
            //DEPRECATED
            $a['zerofill'] = $this->zerofill;
        }
        
        if($this->charset){
            $a['charset'] =  $this->charset;
        }
        if($this->collate){
            $a['collate'] = $this->collate;
        }
        if($this->primary){
            $a['primary'] = $this->primary;
        }elseif($this->unique){
            $a['unique'] = $this->unique;
        }
        
        if(isset($this->options['is_nullable'])){
            $a['is_nullable'] = $this->is_nullable;
        }
        if($this->auto_increment){
            $a['auto_increment'] = $this->auto_increment;
        }else{
            if(isset($this->options['default'])){
                $a['default'] = $this->default;
            }
            if($this->on_update_current_timestamp){
                $a['on_update_current_timestamp'] = $this->on_update_current_timestamp;
            }
        }
        if(isset($this->options['comment'])){
            $a['comment'] = $this->comment;
        }
        if($this->column_format){
            $a['column_format'] = $this->column_format;
        }
        if($this->storage){
            $a['storage'] = $this->storage;
        }
        if($this->references){
            $a['references'] = $this->references;
        }
        if($this->generated){
            $a['generated'] = $this->generated;
            if($this->virtual){
                $a['virtual'] = $this->virtual;
            }
            if($this->stored){
                $a['stored'] = $this->stored;
            }
            if($this->generation_expression){
                $a['generation_expression'] = $this->generation_expression;
            }
        }
        if($this->first){
            $a['first'] = true;
        } elseif($this->after){
            $a['after'] = $this->after;
        }
        return $a;
    }
    
    public function definition() {
        $f =array();
        $type = $this->getDataType();
        if($type){
            $f[] = $type;
        }else{
            return '';
        }
        if($this->charset){
            $f[] = 'CHARACTER SET ' . $this->charset;
        }
        if($this->collate){
            $f[] = 'COLLATE '.$this->collate;
        }
        if($this->generated){
            $f[] = 'GENERATED ALWAYS AS';
            $f[] = "($this->expr)";
            if($this->virtual){
                $f[] = 'VIRTUAL';
            }elseif($this->stored){
                $f[] = 'STORED';
            }
            if(isset($this->options['is_nullable'])){
                if($this->is_nullable){
                    $f[] = 'NULL';
                }else{
                    $f[] = 'NOT NULL';
                }
            }
            
            if($this->comment) $f[] = "COMMENT '{$this->comment}'";
        }else{ 
            
            if(isset($this->options['is_nullable'])){
                if($this->is_nullable){
                    $f[] = 'NULL';
                }else{
                    $f[] = 'NOT NULL';
                }
            }
            if($this->auto_increment){
                $f[] = 'AUTO_INCREMENT';
            }else{
                if(isset($this->options['default'])){
                    $u = strtoupper($this->default);
                    if(in_array($u,array('CURRENT_TIMESTAMP','NOW','CURRENT_TIMESTAMP()','NOW()','NULL'))){
                        $f[] = "DEFAULT $u";
                    }else{
                        $f[] = "DEFAULT '{$this->default}'";
                    }
                }
                if($this->on_update_current_timestamp){
                    $f[] = "on update CURRENT_TIMESTAMP";
                }
            }
            
            if($this->comment) $f[] = "COMMENT '{$this->comment}'";
            if($this->column_format){
                $this->column_format = strtoupper($this->column_format);
                if(in_array($this->column_format,array('FIXED','DYNAMIC','DEFAULT')))
                    $f[] = 'COLUMN_FORMAT ' . $this->column_format;
            }
            if($this->storage){
                $this->storage = strtoupper($this->storage);
                if(in_array($this->storage,array('DISK','MEMORY','DEFAULT')))
                    $f[] = 'STORAGE ' . $this->storage;
            }
            
            if($this->REFERENCES){
                $f[] = $this->REFERENCES;
            }
        }
        return implode(' ',$f);
    }
    public function setName($data){
        $this->options['name'] = $data;
    }
    public function nullable($data = true){
        $this->options['is_nullable'] =  (bool)$data;
        return $this;
    }
    
    public function getDataType(){
        $f =array();
        if($this->data_type){
            $d = $this->data_type;
            if($this->length){
                $f[] = "$d($this->length)";
            }elseif($this->precision && $this->scale){
                $f[] = "$d($this->precision,$this->scale)";
            }elseif($this->precision && $this->scale === 0){
                $f[] = "$d($this->precision,0)";
            }elseif($this->precision){
                $f[] = "$d($this->precision)";
            }elseif($this->scale){
                $f[] = "$d($this->scale)";
            }else{
                $f[] = "$d";
            }
        }else{
            return '';
        }
        if($this->unsigned){
            $f[] = 'UNSIGNED';
        }
        if($this->zerofill){
            //DEPRECATED
            //$f[] = 'ZEROFILL';
        }
        return implode(' ',$f);
    }
    public function setType($data){
        if(!$data){
            return;
        }
        $data = strtolower($data);
        $dbType = strtok($data, '(), ');
        $length = strtok('(), ');
        $fixed = null;
        switch ($dbType) {
            case 'char':
            case 'binary':
                $fixed = true;
                break;
            case 'float':
            case 'double':
            case 'real':
            case 'numeric':
            case 'decimal':
                if(preg_match('([A-Za-z]+\(([0-9]+)\,([0-9]+)\))', $data, $match)) {
                    $this->options['precision'] = (int)$match[1];
                    $this->options['scale'] = (int)$match[2];
                    $length = null;
                    $this->options['data_type_definition'] = $data;
                }
                break;
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'integer':
            case 'bigint':
                //break; //LENGTH is DEPREACTED FOR iinteger columns
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
            case 'blob':
            case 'year':
                $length = null;
                break;
        }
        if($dbType){
            $this->options['data_type'] = $dbType;
            if (empty($this->options['data_type_definition'])){
                $this->options['data_type_definition'] = $data;
            }
        }
        if($length){
            $this->options['length'] = (int)$length;
            $this->options['data_type_definition'] = $data;
        }
        if(strpos($data, 'unsigned') !== false){
            $this->options['unsigned'] = true;
            $this->options['data_type_definition'] = $data;
        }
        if(strpos($data, 'zerofill') !== false){
            //DEPRECATED
            $this->options['zerofill'] = true;
        }
    }
    
    public function setOptions($options){
        //this should not be public
        $this->setIndexOptions($options);
        $this->setColumnOptions($options);
    }
    protected function setColumnOptions($options){
        if(!is_array($options)){
            return;
        }
        $options = array_change_key_case($options,CASE_LOWER);
        unset($options['table_catalog']);
        
        foreach($options as $key=>$value){
            if ($value === null){
                continue;
            }
            if(in_array($key,array('column_type','datatype','data_type','data_type_definition'))){
                $this->setType($value);
            }elseif(in_array($key,array('collate','collation','collation_name'))){
                $this->options['collate'] = $value;
            }elseif(in_array($key,array('table_schema','schema','table','table_name','tablename'))){
                $this->options['tablename'] = $value;
            }elseif(in_array($key,array('charset','char_set','character set','characterset','character_set'))){
                $this->options['charset'] = $value;
            }elseif(in_array($key,array('is_nullable','nullable','null'))){
                if(is_bool($value)){
                    $this->options['is_nullable'] = $value;
                }elseif(strtoupper($value) == 'YES'){
                    $this->options['is_nullable'] = true;
                }elseif(strtoupper($value) == 'NO'){
                    $this->options['is_nullable'] = false;
                }else{
                    $this->options['is_nullable'] = !!$value;
                }
            }elseif(in_array($key,array('not null','not_null'))){
                if(is_bool($value)){
                    $this->options['is_nullable'] = !$value;
                }elseif(strtoupper($value) == 'YES'){
                    $this->options['is_nullable'] = false;
                }elseif(strtoupper($value) == 'NO'){
                    $this->options['is_nullable'] = true;
                }else{
                    $this->options['is_nullable'] = !$value;
                }
            }elseif(in_array($key,array('is_visible','visible'))){
                if(is_bool($value)){
                    $this->options['is_visible'] = $value;
                }elseif(strtoupper($value) == 'YES'){
                    $this->options['is_visible'] = true;
                }elseif(strtoupper($value) == 'NO'){
                    $this->options['is_visible'] = false;
                }else{
                    $this->options['is_visible'] = !!$value;
                }
            }elseif(in_array($key,array('auto_increment','autoincrement'))){
                $this->options['auto_increment'] = $value;
            }elseif(in_array($key,array('on_update_current_timestamp'))){
                $this->options['on_update_current_timestamp'] = $value;
            }elseif(in_array($key,array('column_format','format'))){
                $this->options['column_format'] = strtoupper($value);
            }elseif(in_array($key,array('storage'))){
                $this->options['storage'] = strtoupper($value);
            }elseif(in_array($key,array('generation_expression','expr'))){
                $this->options['generated'] = true;
                $this->options['generation_expression'] = $value;
            }elseif(in_array($key,array('name','field','column_name','field_name'))){
                $this->options['name'] = $value;
            }elseif(in_array($key,array('withparser','with_parser','parser'))){
                $this->options['parser'] = $value;
            }elseif(in_array($key,array('foreign','fk','foreignkey','foreign_key'))){
                if(is_string($value) && !($value=='1' || $value =='0' || strtoupper($value)=='YES' || strtoupper($value)=='NO')){
                    $this->options['name'] = $value;
                    $this->options['foreign'] = true;
                }else{
                    $this->options['foreign'] = !!$value;
                }
            }elseif(in_array($key,array('columns','fields'))){
                $this->options['fields'] = $value;
            }elseif(in_array($key,array('index_comment'))){
                $this->options['comment'] = $value;
            }elseif(in_array($key,array('column_default'))){
                $this->options['default'] = $value;
            }elseif(in_array($key,array('index_type','indextype','using'))){
                $this->options['index_type'] = $value;
            }elseif(in_array($key,array('numeric_precision','precision'))){
                $this->options['precision'] = (int)$value;
            }elseif(in_array($key,array('numeric_scale','scale'))){
                $this->options['scale'] = (int)$value;
            }else{
                //default,comment,references,generated,virtual,stored
                //primary,unique
                $this->options[$key] = $value;
            }
        }
    }
    
    public function setIndexOptions($options){
        if(!is_array($options)){
            return;
        }
        $options = array_change_key_case($options,CASE_LOWER);
        unset($options['table']);
        foreach($options as $key=>$value){
            if ($value === null){
                continue;
            }
            if(in_array($key,array('is_nullable','nullable','null'))){
                if(is_bool($value)){
                    $this->options['is_nullable'] = $value;
                }elseif(strtoupper($value) == 'YES'){
                    $this->options['is_nullable'] = true;
                }elseif(strtoupper($value) == 'NO'){
                    $this->options['is_nullable'] = false;
                }else{
                    $this->options['is_nullable'] = !!$value;
                }
            }elseif(in_array($key,array('not null','not_null'))){
                if(is_bool($value)){
                    $this->options['is_nullable'] = !$value;
                }elseif(strtoupper($value) == 'YES'){
                    $this->options['is_nullable'] = false;
                }elseif(strtoupper($value) == 'NO'){
                    $this->options['is_nullable'] = true;
                }else{
                    $this->options['is_nullable'] = !$value;
                }
            }elseif(in_array($key,array('is_visible','visible'))){
                if(is_bool($value)){
                    $this->options['is_visible'] = $value;
                }elseif(strtoupper($value) == 'YES'){
                    $this->options['is_visible'] = true;
                }elseif(strtoupper($value) == 'NO'){
                    $this->options['is_visible'] = false;
                }else{
                    $this->options['is_visible'] = !!$value;
                }
            }elseif(in_array($key,array('columns','fields','key_part'))){
                $this->setKeyPart($value);
            }elseif($key =='seq_in_index'){
                $this->options['seq_in_index'] = (int)$value;
            }elseif(in_array($key,array('index_type','type','using'))){
                $this->options['index_type'] = strtoupper($value);
            }elseif(in_array($key,array('field_name','column_name','field','column'))){
                $this->options['column_name'] = $value;
            }elseif(in_array($key,array('name','index_name','key_name'))){
                $this->setName($value);
            }else{
                $this->options[$key] = $value;
            }
        }
        
    }
    public function setKeyPart($value) {
        $ex = explode(',',$value);
        $f = [];
        foreach($ex as $value){
            $value = trim($value,' ` ');
            $f[]  = "`{$value}`";
        }
        $this->options['key_part'] = implode(',',$f);
    }
    public function is_numeric(){
        switch ($this->data_type) {
            case 'float':
            case 'double':
            case 'real':
            case 'numeric':
            case 'decimal':
                return true;
                break;
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'integer':
            case 'bigint':
                return true;
            case 'year':
                return true;
        }
        return false;
    }
}