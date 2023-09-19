<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;

class ddl_fields {
    private $data = array();
    public function addOption($text) {
        $field = new ddl_field_item();
        $field->type = "OPTION";
        $field->value = $text;
        $this->data[] = $field;
        return $field;
    }
    public function addCheck($expr) {
        $field = new ddl_field_item();
        $field->type = "CHECK";
        $field->expr = $expr;
        $this->data[] = $field;
        return $field;
    }
    public function add($fieldName, $options =array()) {
        $field = new ddl_field_item($options);
        $field->type = "COLUMN";
        $field->name = $fieldName;
        $this->data[] = $field;
        return $field;
    }
    public function addColumn($fieldName, $alias='') {
        $field = new ddl_field_item();
        $field->type = FIELD_COL;
        if(is_array($fieldName)){
            $c = count($fieldName);
            if($c==1)
                $field->expr = trim(array_unshift($fieldName),'`');
            elseif($c==2){
                $field->tablename = trim(array_unshift($fieldName),'`');
                $field->expr = trim(array_unshift($fieldName),'`');
            }elseif($c==3){
                $field->dbname = trim(array_unshift($fieldName),'`');
                $field->tablename = trim(array_unshift($fieldName),'`');
                $field->expr = trim(array_unshift($fieldName),'`');
            }
        }else
            $field->expr = trim($fieldName,'`');
        $field->alias = $alias;  
        $this->data[] = $field;
    }
    public function addExpression($expr, $alias='') {
        $field = new ddl_field_item();
        $field->type = FIELD_EXP;
        $field->expr = $expr;
        $field->alias = $alias;  
        $this->data[] = $field;
    }
    public function addAll($tablename='') {
        $field = new ddl_field_item();
        $field->type = FIELD_EXP;
        if($tablename){
            if(is_scalar($tablename)){
                $field->expr = "`{$tablename}`.*";
            }elseif( $tablename instanceof tableItem){
                if($tablename->alias)
                    $field->expr =& $tablename->alias ;
                else
                    $field->expr =& $tablename->tablename ;
            }
        }
        else
            $field->expr = '*';
        $field->alias = '';  
        $this->data[] = $field;
    }
    /**
     * fields::addColumnpublic function()
     * 
     * @param mixed $functionName
     * CONCAT
     * MD5
     * @param mixed $alias
     * @param mixed $fields
     * @return void
     */
    public function addColumnpublic function($functionName, $alias, $fields) {
        $a = func_get_args();
        $functionName = trim($functionName,'()');
        array_shift($a);
        $alias = trim($alias,'`');
        array_shift($a);
        $expr = implode(',',$a);
        
        $field = new ddl_field_item();
        $field->type = FIELD_EXP;
        $field->expr = "$functionName($expr)";
        $field->alias = $alias;  
        $this->data[] = $field;
    }
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function __construct() {
    }
    public function asAlterDefinitions() {
        return new ddl_field_alter_definitons($this->data);
    }
    public function asCreateDefinitions() {
        return new ddl_field_create_definitons($this->data);
    }
    public function asColumnlistDefinitions() {
        return new ddl_field_column_definitons($this->data);
    }
    
    public function toString($type=FIELD_COL) {
        if($this->type == FIELD_COL){
            $f=array();
            foreach($this->data as $field){
                if($field->toString(FIELD_COL))
                    $f[] = $field->toString(FIELD_COL);
            }
            return implode(', ', $f);
        }else{
            if(count($this->data))
                return implode(', ', $this->data);
            else
                return '';
        }
    }
    public function __toString() {
        $w = $this->toString();
        if($w)
            return "$w";
        else
            return '';
    }
}

class ddl_field_item{
    private $options = array();
    public function __construct($options=array()) {
        $this->setOptions($options);
        if(!isset($options['type'])){
            $this->options['type'] = FIELD_COL;
        } 
    }
    public function setOptions($options){
        $options = array_change_key_case($options,CASE_LOWER);
        unset($options['table_catalog']);
        foreach($options as $key=>$value){
            if(in_array($key,array('column_type','datatype','data_type','data_type_definition'))){
                $this->setType($value);
            }elseif(in_array($key,array('collate','collation','collation_name'))){
                $this->options['collate'] = $value;
            }elseif(in_array($key,array('charset','char_set','character set','characterset','character_set'))){
                $this->options['charset'] = $value;
            }elseif(in_array($key,array('dbname','table_schema','schema','db','database','database_name'))){
                $this->options['dbname'] = $value;
            }elseif(in_array($key,array('table','table_name','tablename'))){
                $this->options['tablename'] = $value;
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
            }elseif(in_array($key,array('auto_increment','autoincrement'))){
                $this->options['auto_increment'] = $value;
            }elseif(in_array($key,array('on_update_current_timestamp'))){
                $this->options['on_update_current_timestamp'] = $value;
            }elseif(in_array($key,array('column_format','format'))){
                $this->options['column_format'] = strtoupper($value);
            }elseif(in_array($key,array('storage'))){
                $this->options['storage'] = strtoupper($value);
            }elseif(in_array($key,array('generation_expression','expr'))){
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
            }elseif(in_array($key,array('index_type','indextype','using'))){
                $this->options['index_type'] = strtoupper($value);
            }elseif(in_array($key,array('columns','fields'))){
                $this->options['fields'] = $value;
            }elseif(in_array($key,array('index_comment'))){
                $this->options['comment'] = $value;
            }elseif(in_array($key,array('column_default'))){
                $this->options['default'] = $value;
            }else{
                //default,comment,references,generated,virtual,stored
                $this->options[$key] = $value;
            }
        }
    }
    public function __get($name) {
        $name =strtolower($name);
        if(in_array($name,array('foreign','fk','foreignkey','foreign_key'))){
            $name = 'foreign';
        }
        if(in_array($name,array('columns'))){
            $name = 'fields';
        }
        if(in_array($name,array('withparser','with_parser'))){
            $name = 'parser';
        }
        
        if(in_array($name,array('index_type','indextype','using'))){
            $name = 'index_type';
        }
        if(isset($this->options[$name])) {
            return $this->options[$name];
        }
        return null;
    }
    public function __set($name, $value) {
        $name =strtolower($name);
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
            $value = !$value;
        }
        if(in_array($name,array('columns'))){
            $name = 'fields';
        }
        
        if(in_array($name,array('withparser','with_parser'))){
            $name = 'parser';
        }
        if(in_array($name,array('index_type','indextype','using'))){
            $name = 'index_type';
            $value = strtoupper($value);
        }
        $this->options[$name] = $value;
    }
    public function setType($data){
        $this->options['data_type_definition'] = $data;
        $data = strtolower($data);
        $dbType = strtok($data, '(), ');
        $length = strtok('(), ');
        $precision = $scale = $fixed = null;
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
                }
                break;
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'integer':
            case 'bigint':
                break;
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
        }
        if($length){
            $this->options['length'] = (int)$length;
        }
        if(strpos($data, 'unsigned') !== false){
            $this->options['unsigned'] = true;
        }
        if(strpos($data, 'zerofill') !== false){
            $this->options['zerofill'] = true;
        }
    }
    public function definition() {
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
            $f[] = 'ZEROFILL';
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
            if($this->primary){
                $f[] = 'PRIMARY KEY';
            }elseif($this->unique){
                $f[] = 'UNIQUE KEY';
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
            
            if($this->auto_increment ){
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
            if($this->primary){
                $f[] = 'PRIMARY KEY';
            }elseif($this->unique){
                $f[] = 'UNIQUE KEY';
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
    public function indexOption() {
        $f =array();
        if($this->key_block_size) $f[] = "KEY_BLOCK_SIZE {$this->key_block_size}";
        if($this->index_type){
            $iType = strtoupper($this->index_type);
        }elseif($this->using){
            $iType = strtoupper($this->using);
        }else{
            $iType = '';
        }
        if(in_array($iType,array('BTREE','HASH'))){
            $f[] = "USING $iType";
        }
        if($this->parser) $f[] = "WITH PARSER {$this->parser}";
        if($this->comment) $f[] = "COMMENT '{$this->comment}'";
        return implode(' ',$f);
    }
    public function toString() {
        if($this->name == '') return '';
        
        
        if(func_num_args())
            $type = func_get_arg(0);
        else
            $type = 'PLAIN';
            
        switch($type){
        case 'CREATE':
            if($this->type=='OPTION'){
                return $this->value;
            }
            if($this->type=='COLUMN'){
                $def = $this->definition();
                if($this->name && $def){
                    return trim("`{$this->name}` {$def} ");
                }    
            }
            if($this->type =='INDEX' || $this->type=='KEY'){
                if($this->constraint){
                    $constraint = "CONSTRAINT `$this->constraint` ";
                }else{
                    $constraint ='';
                }
                
                if($this->fields){
                    $iCol = "({$this->fields})";
                }else{
                    $iCol ='';
                }
                 
                $iOption = $this->indexOption();   
        
                if($this->primary || (strtoupper($this->name)=='PRIMARY')){
                    return trim("{$constraint}PRIMARY KEY {$iCol} {$iOption}");
                }
    
                if($this->foreign){
                    $rd = $this->REFERENCES?$this->REFERENCES :'';
                    return trim("{$constraint}FOREIGN KEY `{$this->name}` {$iCol} {$rd}");
                }
                
                if($this->unique){
                    return trim("{$constraint}UNIQUE {$this->type} `{$this->name}` {$iCol} {$iOption}");
                }
                if($this->name){
                    $predicate ='';
                    if($this->fulltext) $predicate ='FULLTEXT ';
                    if($this->spatial) $predicate ='SPATIAL '; 
                    return trim("{$predicate}{$this->type} `{$this->name}` {$iCol} {$iOption}");
                }
            }
            if($this->type=='CHECK'){
                if($this->expr){
                    return trim("CHECK ({$this->expr})");
                }    
            }
            
        break;
        case 'ALTER':
            if($this->type=='OPTION'){
                return $this->value;
            }
            if($this->mode=='DROP'){
                if($this->type =='INDEX' || $this->type=='KEY'){
                    if($this->primary || (strtoupper($this->name)=='PRIMARY')){
                        return 'DROP PRIMARY KEY';
                    }
                    if($this->foreign){
                        return "DROP FOREIGN KEY `{$this->name}`";
                    }
                    if($this->name){
                        return "DROP {$this->type} `{$this->name}`";
                    }
                }
                if($this->type =='COLUMN'){
                    if($this->name){
                        return "DROP COLUMN `{$this->name}`";
                    }
                }
                if($this->name){
                    return "DROP {$this->name}";
                }
                return 'DROP';
            }
                
            if($this->type=='COLUMN'){
                $def = $this->definition();
                $foa ='';
                if($this->after){
                    $foa = "AFTER `$this->after`";
                }elseif($this->first){
                    $foa = 'FIRST';
                }
                if($this->mode=='CHANGE'){
                    if($def){
                        if($this->new_name){
                            $new_name = $this->new_name;
                        }else{
                            $new_name = $this->name;
                        }
                        return trim("CHANGE COLUMN `{$this->name}` `{$new_name}` {$def} {$foa}");
                    }else{
                        $this->mode='ALTER';
                    }
                }
                
                if($this->mode=='MODIFY'){
                    if($def){
                        return trim("MODIFY COLUMN `{$this->name}` {$def} {$foa}");
                    }else{
                        $this->mode='ALTER';
                    }
                }
                
                if($this->mode=='ALTER'){
                    if($this->default){
                        $u = strtoupper($this->default);
                        if(in_array($u,array('CURRENT_TIMESTAMP','NOW','CURRENT_TIMESTAMP()','NOW()','NULL'))){
                            $def = "SET DEFAULT $u";
                        }else{
                            $def = "SET DEFAULT '{$this->default}'";
                        }
                    }else{
                        $def = "DROP DEFAULT";
                    }
                    return "ALTER COLUMN `{$this->name}` {$def}";
                }
                if($this->mode=='ADD' && $def){
                    return trim("ADD COLUMN `{$this->name}` {$def} {$foa}");
                }    
            }
            if($this->mode=='ADD'){
                if($this->type =='INDEX' || $this->type=='KEY'){
                    
                    if($this->constraint){
                        $constraint = "CONSTRAINT `$this->constraint` ";
                    }else{
                        $constraint ='';
                    }
                    
                    if($this->fields){
                        $iCol = "({$this->fields})";
                    }else{
                        $iCol ='';
                    }
                     
                    $iOption = $this->indexOption();   
            
                    if($this->primary || (strtoupper($this->name)=='PRIMARY')){
                        return trim("ADD {$constraint}PRIMARY KEY {$iCol} {$iOption}");
                    }
        
                    if($this->foreign){
                        $rd = $this->REFERENCES?$this->REFERENCES :'';
                        return trim("ADD {$constraint}FOREIGN KEY `{$this->name}` {$iCol} {$rd}");
                    }
                    
                    if($this->unique){
                        return trim("ADD {$constraint}UNIQUE {$this->type} `{$this->name}` {$iCol} {$iOption}");
                    }
                    if($this->name){
                        $predicate ='';
                        if($this->fulltext) $predicate ='FULLTEXT ';
                        if($this->spatial) $predicate ='SPATIAL '; 
                        return trim("ADD {$predicate}{$this->type} `{$this->name}` {$iCol} {$iOption}");
                    }
                }
            }
  
  
        break;
        case 'COLUMN':
            $a=array();
            if($this->dbname) $a[] = "`$this->dbname`";
            if($this->tablename) $a[] = "`$this->tablename`";
            $a[] = "`$this->name`";
            $f = implode('.',$a);
            if($this->alias){
                return "$f AS $this->alias";
            }else{
                return $f;
            }
        break;
        default:
            if($this->alias){
                return "$this->expr AS $this->alias";
            }else{
                return "$this->expr";
            }
        } 
        
     
            
    }
    public function __toString(){
        return $this->toString();
    }
}

class ddl_field_alter_definitons{
    private $data = array();
    public function __construct($fields) {
        $this->data = $fields;
    }
    public function toString() {
        $ff=array();
        foreach($this->data as $field){
            $ff[] = $field->toString('ALTER');
        }
        return implode(', ', $ff);
    }
}
class ddl_field_create_definitons{
    private $data = array();
    public function __construct($fields) {
        $this->data = $fields;
    }
    
    public function toString() {
        $ff=array();
        foreach($this->data as $field){
            $ff[] = $field->toString('CREATE');
        }
        return implode(', ', $ff);
    }
}
class ddl_field_column_definitons{
    private $data = array();
    public function __construct($fields) {
        $this->data = $fields;
    }
    
    public function toString() {
        $ff=array();
        foreach($this->data as $field){
            $ff[] = $field->toString(FIELD_COL);
        }
        return implode(', ', $ff);
    }
}