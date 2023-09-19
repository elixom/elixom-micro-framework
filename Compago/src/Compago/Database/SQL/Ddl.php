<?php

namespace Compago\Database\SQL;
use Compago\Database\Core\OptionBag;
use Compago\Database\Core\SpParameterBag;
use Compago\Database\Core\PartitionBag;

class Ddl extends \Compago\Database\SQL\ColumnCollection{
    protected $type = 'CREATE';
    protected $data = [];
    protected $options;
    protected $parameters;
    protected $partition;
    
    protected static $_DATA_FIELDS = ['table','view','event','logfile',
    'function','procedure',
    'trigger','index','server','database',
                                'tablespace','soname','returns',
                                'definer','security','body','wrapper','select','like',
                                'algorithm','lock',
                                'mode','with','temporary',
                                'triggerevent','follows','preceeds'];
    
    public function __construct($type) {
        $type = strtoupper($type);
        if (in_array($type,['CREATE','ALTER','DROP','TRUNCATE'])){
            $this->type = $type;
        } else {
            throw new \Exception('Type of DDL statement must be one of CREATE,ALTER,DROP,TRUNCATE');
        }
        $this->options = new OptionBag;
        $this->parameters = new SpParameterBag;
        $this->partition = new PartitionBag;
    }
    protected static function tr_field_name($name) {
        $name = strtolower($name);
        if($name =='collation') $name ='collate';
        if($name =='characterset') $name ='charset';
        if($name =='character_set') $name ='charset';
        if($name =='char_set') $name ='charset';
        if($name =='return') $name ='returns';
        if($name =='index_type') $name ='indextype';
        return $name;
    }
    public function __get($name) {
        $name = self::tr_field_name($name);        
        if (in_array($name, self::$_DATA_FIELDS)){
            if(isset($this->data[$name])){
                return $this->data[$name];
            }
            return null;
        }
        if(($f = $this->options->get($name)) !== null){
            return $f;
        }
        return null;
    }
    
    public function __set($name, $value) {
        $name = self::tr_field_name($name);
        
        if(in_array($name,array('algorithm','lock','predicate','mode','with'))){
            $value = strtoupper((string)$value);
        }
        if (in_array($name, self::$_DATA_FIELDS)){
            $this->data[$name] = $value;
            return null;
        }
        if(in_array($name,array('engine','collate','charset','comment','row_format'))){
            $this->options->add($name,(string)$value);
            return;
        }
        if(in_array($name,array('auto_increment','autoincrement'))){
            $this->options->add('auto_increment',(int)$value);
            return;
        }
        
        
        
    }

    public function __call($name, $arguments) {
        $name = self::tr_field_name($name);
        
        if (in_array($name, self::$_DATA_FIELDS)){
            $this->__set($name, $arguments[0]);
            return $this;
        }
        /*if(in_array($name,array('engine','collate','charset','comment'))){
            if(count($arguments)){
                $this->options()->add($name,(string)$arguments[0]);
                return $this;
            }else{
                return $this->options()->get($name);
            }
        }
        if(in_array($name,array('auto_increment','autoincrement'))){
            $name = 'auto_increment';
            if(count($arguments)){
                $this->options()->add($name,(int)$arguments[0]);
                return $this;
            }else{
                return $this->options()->get($name);
            }
        }*/
        if ($name == 'predicate'){
            $this->options->add($arguments[0]);
            return $this;
        }
        if(count($arguments)){
            $this->options->add($name,$arguments[0]);
            return $this;
        }else{
            return $this->options->get($name);
        }
    }
    
    public function ifExists() {
        $this->options->add('IF EXISTS');
        return $this;
    }
    public function ifNotExists() {
        $this->options->add('IF NOT EXISTS');
        return $this;
    }
    public function options() {
        if($n = func_num_args()){
            if($n==1){
                $a = func_get_arg(0);
                if(is_array($a)){
                    foreach($a as $k=>$v){
                        if(is_string($k)){
                            $this->options->add($k,$v);
                        }else{
                            $this->options->add($v);
                        }
                    }
                }
            }else{
                foreach(func_get_args() as $v) $this->options->add($v);
            }
            return $this;
        }else{
            return $this->options;
        }
    }
    public function parameters() {
        if($n = func_num_args()){
            if($n==1){
                $a = func_get_arg(0);
                if(is_array($a)){
                    foreach($a as $k=>$v){
                        if(is_string($k)){
                            $this->parameters->add($k,$v);
                        }else{
                            $this->parameters->add($v);
                        }
                    }
                }
            }else{
                foreach(func_get_args() as $v) $this->parameters->add($v);
            }
            return $this;
        }else{
            return $this->parameters;
        }
    
    }
    public function partition() {
        if($n = func_num_args()){
            if($n==1){
                $a = func_get_arg(0);
                if(is_array($a)){
                    foreach($a as $k=>$v){
                        if(is_string($k)){
                            $this->partition->add($k,$v);
                        }else{
                            $this->partition->add($v);
                        }
                    }
                }
            }else{
                foreach(func_get_args() as $v) $this->partition->add($v);
            }
            return $this;
        }else{
            return $this->partition;
        }
    
    }
    public function toString() {
        $type = (func_num_args())?func_get_arg(0):$this->type;
        $parts =array();
        $charset = $collation = $engine = '';
        if((NULL !==$this->options)){
            $engine = $this->options->get('engine');
            $collation = $this->options->get('collation');
            $charset = $this->options->get('charset');
        }
        $add_post_field_comma = false;
        
        switch($type){
        case 'CREATE':
        case 'ALTER':
            if($type == 'ALTER'){
                $is_create = false;
                $parts[] = 'ALTER';
            }else{
                $is_create = true;
                $parts[] = 'CREATE';
            }
            if($this->database){
                $parts[] = 'DATABASE';
                if($is_create && $this->options->get('IF NOT EXISTS')) $parts[] = 'IF NOT EXISTS';
                $parts[] = "`$this->database`";
                if($charset) $parts[] = 'DEFAULT CHARACTER SET = ' . $charset;
                if($collation) $parts[] = 'DEFAULT COLLATE = ' . $collation;
            }elseif($this->function || $this->procedure){
                if($is_create){
                    if($this->definer && !$this->soname){
                        $parts[] = "DEFINER = $this->definer";
                    }else{
                        if($this->options->get('AGGREGATE')) $parts[] = 'AGGREGATE';
                    }
                }
                if($this->function){
                    $parts[] = 'FUNCTION';
                    $parts[] = "`$this->function`";
                }else{
                    $parts[] = 'PROCEDURE';
                    $parts[] = "`$this->procedure`";
                }
                if ($this->options->toString()){
                    $newOps = new OptionBag($this->options->toArray(['COMMENT','LANGUAGE','DETERMINISTIC','SQL SECURITY']));
                    if ($f = $this->options->anyOf(['CONTAINS SQL','NO SQL','READS SQL DATA','MODIFIES SQL DATA'])){
                        $newOps->add($f,$this->options->get($f));
                    }
                    $spOpts = $newOps->toString();
                } else {
                    $spOpts = '';
                }
                if($is_create){
                    if(!$this->soname){
                        $w = $this->parameters->toString();
                        $parts[] = "($w)";
                    }
                    if($this->returns){
                        $parts[] = 'RETURNS ' . $this->returns;
                    }
                    if($this->soname){
                        $parts[] = "SONAME '$this->soname'";
                    }else{
                        if($spOpts) $parts[] = $spOpts;
                        if($this->body) $parts[] = "BEGIN $this->body END";
                    }
                } else {
                    if($spOpts) $parts[] = $spOpts;
                }
                
            }elseif($this->event){
                #MUST HAPPEN before table
                if($this->definer){
                    $parts[] = "DEFINER = $this->definer";
                }
                $parts[] = 'EVENT';
                if($is_create && $this->options->get('IF NOT EXISTS')) $parts[] = 'IF NOT EXISTS';
                $parts[] = "`$this->event`";
                if ($this->options->toString()){
                    $newOps = new OptionBag($this->options->toArray(['COMMENT','ON SCHEDULE','ON COMPLETION']));
                    if ($f = $this->options->anyOf(['ENABLE','DISABLE','DISABLE ON SLAVE'])){
                        $newOps->add($f,$this->options->get($f));
                    }
                    $parts[] = $newOps->toString();
                }
                if($this->body) $parts[] = "DO BEGIN $this->body END";
            }elseif($this->logfile){
                $parts[] = 'LOGFILE GROUP';
                $parts[] = "`$this->logfile`";
                
                    /* ALTER: ADD UNDOFILE 'file_name'    [INITIAL_SIZE [=] size]    [WAIT]*/
                    /* CREATE: ADD UNDOFILE 'undo_file'    [INITIAL_SIZE [=] initial_size] [UNDO_BUFFER_SIZE [=] undo_buffer_size]    [REDO_BUFFER_SIZE [=] redo_buffer_size] [NODEGROUP [=] nodegroup_id]     [WAIT]*/
                    if($f =$this->options->toString()) $parts[] = "{$f}";
                
                //if($engine) $parts[] = "ENGINE $engine";
                
            }elseif($this->trigger){
                //CREATE ONLY
                if($this->definer){
                    $parts[] = "DEFINER = $this->definer";
                }
                $parts[] = 'TRIGGER';
                $parts[] = "`$this->trigger`";
                if ($f = $this->options->get('time')){
                    if(in_array($f,array('BEFORE','AFTER'))) $parts[] = $f;
                }
                if(in_array($this->triggerEvent,array('INSERT','UPDATE','DELETE'))) $parts[] = $this->triggerEvent;;
                if($this->table){
                    $parts[] = "ON `$this->table` FOR EACH ROW";
                }
                if($this->follows){
                    $parts[] = "FOLLOWS $this->follows";
                }elseif($this->preceeds){
                    $parts[] = "PRECEDES $this->preceeds";
                }
                if($this->body) $parts[] = "BEGIN $this->body END";
            }elseif($this->tablespace){
                $parts[] = 'TABLESPACE';
                $parts[] = "`$this->tablespace`";
                //if((NULL !==$this->options)){
                    /*ALTER: {ADD|DROP} DATAFILE 'file_name' [INITIAL_SIZE [=] size] [WAIT]*/
                    /*CREATE: 
                    InnoDB and NDB:
                        ADD DATAFILE 'file_name'
                    
                      InnoDB only:
                        [FILE_BLOCK_SIZE = value
                      NDB only:
                        USE LOGFILE GROUP logfile_group
                        [EXTENT_SIZE [=] extent_size]
                        [INITIAL_SIZE [=] initial_size]
                        [AUTOEXTEND_SIZE [=] autoextend_size]
                        [MAX_SIZE [=] max_size]
                        [NODEGROUP [=] nodegroup_id]
                        [WAIT]
                    */
                    if($f =$this->options->toString()) $parts[] = "{$f}";
                //}
                //if($engine) $parts[] = "ENGINE = $engine";
                
            }elseif($this->server){
                $parts[] = 'SERVER';
                $parts[] = "`$this->server`";
                if($is_create && $this->wrapper) $parts[] = "FOREIGN DATA WRAPPER '$this->wrapper'";
                if($this->options->toString()){
                    $a = $this->options->toArray();
                    $r = array();
                    foreach(array('HOST','DATABASE','USER','PASSWORD','SOCKET','OWNER') as $k){
                        if(isset($a[$k])){
                            $r[] ="$k '{$a[$k]}'";
                        }
                    }
                    $k = 'PORT';
                    if(isset($a[$k])){
                        $r[] ="$k {$a[$k]}";
                    }
                    $f = implode(' ',$r);
                    if($f) $parts[] = "OPTIONS ({$f})";
                }
            }elseif($this->index){
                //create only
                if($is_create && ( $f = $this->options->anyOf(['UNIQUE','FULLTEXT','SPATIAL']))){
                     $parts[] = $f;
                }
                $parts[] = 'INDEX';
                $parts[] = "`$this->index`";
                $parts[] = 'ON';
                $parts[] = "`$this->table`";
                
                //TODO ?(col_name [(length)] [ASC | DESC])
                
                if ($f = parent::toString('COLUMN')){
                    $parts[] = "({$f})";
                }
                if($this->indexType) $parts[] = 'USING ' . $this->indexType;
                if ($this->options->toString()){
                    $newOps = new OptionBag($this->options->toArray(['COMMENT','KEY_BLOCK_SIZE','WITH PARSER']));
                    if ($f = $this->options->anyOf(['VISIBLE','INVISIBLE'])){
                        $newOps->add($f,$this->options->get($f));
                    }
                    if(!$this->indexType){
                        if ($f = $this->options->anyOf(['INDEXTYPE','USING'])){
                            $newOps->add('USING',$this->options->get($f));
                        }
                    }
                    $parts[] = $newOps->toString();
                }
                
                if($this->algorithm){
                    if(in_array($this->algorithm,array('DEFAULT','INPLACE','COPY'))) $parts[] = 'ALGORITHM = ' . $this->algorithm;;
                }elseif($this->lock){
                    if(in_array($this->lock,array('DEFAULT','NONE','SHARED','EXCLUSIVE'))) $parts[] = 'LOCK = ' . $this->lock;;
                }
            }elseif($this->view){
                if($is_create && ( $f = $this->options->anyOf(['REPLACE','OR REPLACE','IF EXISTS']))){
                     $parts[] = 'OR REPLACE';
                }
                if($this->algorithm){
                    if(in_array($this->algorithm,array('UNDEFINED','MERGE','TEMPTABLE'))) $parts[] = 'ALGORITHM = ' . $this->algorithm;;
                }
                if($this->definer){
                    $parts[] = "DEFINER = $this->definer";
                }
                if($this->security){
                    $parts[] = "SQL SECURITY  $this->security";
                }
                $parts[] = 'VIEW';
                $parts[] = "`$this->view`";
                if ($f = parent::toString('COLUMN')){
                    $parts[] = "({$f})";
                }
                
                if($this->select){
                    $parts[] = "AS $this->select";
                }
                
                if($this->with){
                    $parts[] = 'WITH';
                    if(in_array($this->with,array('CASCADED','LOCAL'))) $parts[] = $this->with;;
                    $parts[] = 'CHECK OPTION';
                }
            }elseif($this->table){
                if($is_create){
                    if($this->temporary) $parts[] = 'TEMPORARY';
                }else{
                    if($this->ignore) $parts[] = 'IGNORE';
                }
                
                $parts[] = 'TABLE';
                if($is_create && $this->options->get('IF NOT EXISTS')) $parts[] = 'IF NOT EXISTS';
                $parts[] = "`$this->table`";
                if($is_create){
                    if($this->like){
                        $parts[] = "(LIKE  $this->like)";
                    }else{
                        if ($f = parent::toString('CREATE')){
                            $parts[] = "({$f})";
                        }
                        if((NULL !==$this->options)){
                            if($f =$this->options->toString()) $parts[] = "{$f}";
                        }
                        if((NULL !==$this->partition)){
                            if($f =$this->partition->toString()) $parts[] = "{$f}";
                        }
                        if($this->ignore) $parts[] = 'IGNORE';
                        elseif($this->replace) $parts[] = 'REPLACE';
                        elseif(in_array($this->mode,array('IGNORE','REPLACE'))) $parts[] = $this->mode;
                        if($this->select){
                            $parts[] = "AS  $this->select";
                        }
                    }
                }else{
                    if ($f = parent::toString('ALTER')){
                        $parts[] = "{$f}";
                        $add_post_field_comma = true;
                    }
                    if($f =$this->options->toString()){
                        if($add_post_field_comma){
                            $parts[] = ",";
                        }
                        $parts[] = "{$f}";
                        $add_post_field_comma = false;
                    }
                    if((NULL !==$this->partition)){
                        if($f =$this->partition->toString()){
                            if($add_post_field_comma){
                                $parts[] = ",";
                            }
                            $parts[] = "{$f}";
                            $add_post_field_comma = false;
                        }
                    }
                }
            }
        
        break;
        case 'TRUNCATE':
            $parts[] = 'TRUNCATE';
            if($this->table){
                $parts[] = 'TABLE';
                $parts[] = "`$this->table`";
            }
        break;
        case 'DROP':
            $parts[] = 'DROP';
            if($this->index){
                $parts[] = 'INDEX';
                $parts[] = "`$this->index`";
                $parts[] = 'ON';
                $parts[] = "`$this->table`";
                if($this->algorithm){
                    if(in_array($this->algorithm,array('DEFAULT','INPLACE','COPY'))) $parts[] = 'ALGORITHM = ' . $this->algorithm;;
                }elseif($this->lock){
                    if(in_array($this->lock,array('DEFAULT','NONE','SHARED','EXCLUSIVE'))) $parts[] = 'LOCK = ' . $this->lock;;
                }
            }elseif($this->table){
                if($this->temporary) $parts[] = 'TEMPORARY';
                $parts[] = 'TABLE';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->table`";
                if(in_array($this->mode,array('CASCADE','RESTRICT'))) $parts[] = $this->mode;;
            }elseif($this->view){
                $parts[] = 'VIEW';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->view`";
                if(in_array($this->mode,array('CASCADE','RESTRICT'))) $parts[] = $this->mode;;
            }elseif($this->event){
                $parts[] = 'EVENT';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->event`";
            }elseif($this->tablespace){
                $parts[] = 'TABLESPACE';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->tablespace`";
                if($engine) $parts[] = 'ENGINE = ' . $engine;
            }elseif($this->logfile){
                $parts[] = 'LOGFILE GROUP';
                $parts[] = "$this->logfile";
                if($engine) $parts[] = 'ENGINE = ' . $engine;
            }elseif($this->database){
                $parts[] = 'DATABASE';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->database`";
            }elseif($this->server){
                $parts[] = 'SERVER';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->server`";
            }elseif($this->function){
                $parts[] = 'FUNCTION';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->function`";
            }elseif($this->procedure){
                $parts[] = 'PROCEDURE';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->procedure`";
            }elseif($this->trigger){
                $parts[] = 'TRIGGER';
                if ($this->options->get('IF EXISTS')) $parts[] = 'IF EXISTS';
                $parts[] = "`$this->trigger`";
            }
            
        break;
        }
        
        return implode(' ', $parts);
    }
    public function hasDefinitions() {
        switch($this->type){
        case 'CREATE':
        case 'ALTER':
            $is_create = ($this->type == 'CREATE');
            if($this->database){
                return true;
            }elseif($this->function || $this->procedure){
                if($is_create){
                    if($this->soname){
                        return true;
                    }else{
                        if($this->body){
                            return true;
                        }
                    }
                }
                if($f =$this->options->toString()){
                    return true;
                }
            }elseif($this->event){
                if($this->body){
                    return true;
                }
            }elseif($this->logfile){
                if($f =$this->options->toString()){
                    return true;
                }
            }elseif($this->trigger){
                if($this->body){
                    return true;
                }
            }elseif($this->tablespace){
                if($f =$this->options->toString()){
                    return true;
                }
            }elseif($this->server){
                if($f =$this->options->toString()){
                    return true;
                }
            }elseif($this->index){
                if (!empty($this->columns)){
                    return true;
                }
            }elseif($this->view){
                if($this->select){
                    return true;
                }
            }
            elseif($this->table){
                if($is_create){
                    if($this->like){
                        return true;
                    }else{
                        if (!empty($this->columns)){
                            return true;
                        }
                        if($this->select){
                            return true;
                        }
                        if($f =$this->options->toString()){
                            return true;
                        }
                        if($f =$this->partition->toString()){
                            return true;
                        }
                    }
                }else{
                    if (!empty($this->columns)){
                        return true;
                    }
                    if($f =$this->options->toString()){
                        return true;
                    }
                    if($f =$this->partition->toString()){
                        return true;
                    }
                }
            }
        
        break;
        case 'TRUNCATE':
            if($this->table){
                 return true;
            }
        break;
        case 'DROP':
            if($this->index){
                if($this->table){
                    return true;
                }
            }elseif($this->table ||$this->view ||$this->event ||$this->tablespace || $this->logfile){
                return true;
            }elseif($this->database || $this->server || $this->function || $this->procedure||$this->trigger){
                return true;
            }
        break;
        }
        return false;
    }
    public function fields() {
        if(func_num_args()){
            if(func_num_args()==1){
                paren::add(func_get_arg(0));
            }else{
                foreach(func_get_args() as $f)
                    paren::add($f);
            }
            return $this;
        }else{
            return $this;
        }
    }
    public function addColumn($name, $options=array()) {
        $options['name'] = $name;
        $options['attribute_type'] = 'COLUMN';
        $field = $this->add($options);
        $field->mode = 'ADD';
        return $field;
    }
    public function addAutoIncrement($name, $options=array()) {
        $options['auto_increment'] = true;
        $options['data_type'] = 'int';
        $options['name'] = $name;
        $options['attribute_type'] = 'COLUMN';
        $field = $this->add($options);
        $field->mode = 'ADD';
        return $field;
    }
    
    public function addGeneratedColumn($name,$expr, $options=array()) {
        $options['name'] = $name;
        $options['generated'] = true;
        $options['attribute_type'] = 'COLUMN';
        $options['generation_expression'] = $expr;
        $field = $this->add($options);
        $field->mode = 'ADD';
        return $field;
    }
    public function addIndex($name, $options=array()) {
        $options['attribute_type'] = 'INDEX';
        $options['name'] = $name;
        $field = $this->add($options);
        $field->mode = 'ADD';
        return $field;
    }
    public function dropIndex($name) {
        $options = array();
        $options['attribute_type'] = 'INDEX';
        $options['name'] = $name;
        $field = $this->add($options);
        $field->mode = 'DROP';
        return $field;
    }
    public function dropColumn($name) {
        $options = array();
        $options['name'] = $name;
        $options['attribute_type'] = 'COLUMN';
        $field = $this->add($options);
        $field->mode = 'DROP';
        return $field;
    }
    public function changeColumn($name, $newName, $options=array()) {
        $options['name'] = $name;
        $options['new_name'] = $newName;
        $options['attribute_type'] = 'COLUMN';
        $field = $this->add($options);
        $field->mode = 'CHANGE';
        return $field;
    }
    public function modifyColumn($name, $options=array()) {
        $options['name'] = $name;
        $options['attribute_type'] = 'COLUMN';
        $field = $this->add($options);
        $field->mode = 'MODIFY';
        return $field;
    }
    public function alterColumn($name, $default) {
        $options = array();
        $options['attribute_type'] = 'COLUMN';
        $options['name'] = $name;
        $options['default'] = $default;
        $field = $this->add($options);
        $field->mode = 'ALTER';
        return $field;
    }
    public function convert_charset($charset) {
        if($charset){
            $this->options()->add('CONVERT TO CHARACTER SET', $charset);
        }else{
            $this->options()->delete('CONVERT TO CHARACTER SET');
        }
        return $this;
    }
}