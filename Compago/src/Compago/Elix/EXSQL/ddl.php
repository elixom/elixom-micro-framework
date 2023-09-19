<?php
/**
 * @author Edwards
 * @copyright 2012
 * 
 * 
 * 
 * DOES NOT IMPLEMENT 
 *   - RENAME  http://dev.mysql.com/doc/refman/5.7/en/rename-table.html
        14.1.5 ALTER INSTANCE Syntax
        

        

//TODO check SQL SECURITY



                $dml->type(DML_CREATE)
                ->table('table_name')
                ->temporary(true)
                ->ifNotExists()
                ->like('other_table');
                
                $dml->type(DML_CREATE)
                ->table('table_name')
                ->temporary(true)
                ->ifNotExists()
                ->ignore(true) | ->replace(true) 
                ->select('query_expression');
                $dml->fields()
                $dml->options()
                $dml->partition()
                
                
                $dml->type(DML_CREATE)
                ->table('table_name')
                ->temporary(true)
                ->ifNotExists()
                $dml->fields()
                $dml->options()
                $dml->partition()
 * 
 */
namespace EXSQL;
include_once('ddl_fields.php');

class ddl{
    private $type = DML_CREATE;
    
    private $predicate = null;
    private $algorithm = null;
    private $lock = null;
    private $temporary = null;
    private $mode = null;
    private $triggerEvent=null;
    private $time=null;
    private $follows=null;
    private $preceeds=null;
    private $wrapper=null;
    private $security =null;
    private $with =null;
    private $select =null;
    private $indexType=null;
    private $like =null;
    private $ignore =null;
    private $replace =null;
    
    private $tables = array();
    private $table = null;
    private $tablespace = null;
    private $database = null;
    private $index=null;
    private $view = null;
    private $event = null;
    private $logfile = null;
    private $function = null;
    private $procedure = null;
    private $trigger = null;
    private $server = null;
    //private $engine = null;
    //private $charset = null;
    //private $collation = null;
    private $returns = null;
    private $soname = null;
    //private $comment = null;
    private $definer = null;
    private $body = null;
    private $options= null;
    private $parameters =null;
    private $fields = null;
    private $partition= null;
    
    public function __toString() {
        return $this->sql();
    }
    public function __construct() {
        if(func_num_args())
            $this->type(func_get_arg(0));
    }
    public function __set($name, $value) {
        $name=strtolower($name);
        if($name =='collation') $name ='collate';
        if($name =='characterset') $name ='charset';
        if($name =='character_set') $name ='charset';
        if($name =='char_set') $name ='charset';
        if($name =='return') $name ='returns';
        
        if(in_array($name,array('function','procedure','trigger','index','server',
                                'tablespace','soname',
                                'definer','body','wrapper','select','like'))){
            $this->$name = (string)$value;
            return;
        }
        if(in_array($name,array('engine','collate','charset','comment','row_format'))){
            $this->options()->add($name,(string)$value);
            return;
        }
        if(in_array($name,array('auto_increment'))){
            $this->options()->add($name,(int)$value);
            return;
        }
        
        if(in_array($name,array('algorithm','lock','predicate','mode','with'))){
            $this->$name = strtoupper((string)$value);
            return;
        }
        if(in_array($name,array('event','returns'))){
            $this->$name((string)$value);
            return;
        }
    }

    public function __call($name, $arguments) {
        $name=strtolower($name);
        if($name =='collation') $name ='collate';
        if($name =='characterset') $name ='charset';
        if($name =='character_set') $name ='charset';
        if($name =='char_set') $name ='charset';
        
        if(in_array($name,array('function','procedure','trigger','index','server',
                                'tablespace','soname',
                                'definer','body','wrapper','select','like'))){
            if(count($arguments)){
                $this->$name = (string)$arguments[0];
                return $this;
            }else{
                return $this->$name;
            }
        }
        if(in_array($name,array('engine','collate','charset','comment'))){
            if(count($arguments)){
                $this->options()->add($name,(string)$arguments[0]);
                return $this;
            }else{
                return $this->options()->get($name);
            }
        }
        if(in_array($name,array('auto_increment'))){
            if(count($arguments)){
                $this->options()->add($name,(int)$arguments[0]);
                return $this;
            }else{
                return $this->options()->get($name);
            }
        }
        if(in_array($name,array('algorithm','lock','with'))){
            if(count($arguments)){
                $this->$name = strtoupper((string)$arguments[0]);
                return $this;
            }else{
                return $this->$name;
            }
        }
        if(count($arguments)){
            $this->options()->add($name,$arguments[0]);
            return $this;
        }else{
            return $this->options()->get($name);
        }
    }

    public function type($sqlType=DML_CREATE) {
        if(func_num_args()){
            $sqlType=strtoupper($sqlType);
            if(in_array($sqlType,array(DML_ALTER,DML_DROP,DML_CREATE,DML_TRUNCATE)))
                $this->type = $sqlType;
            return $this;
        }else{
            return $this->type;
        }
        
    }
    
    public function database() {
        if(func_num_args()){
            $this->database = (string)func_get_arg(0);
            return $this;
        }else{
            return $this->database;
        }
    }
    public function view() {
        if($n = func_num_args()){
            
            if($n == 1){
                $aa = func_get_arg(0);
            }else{
                $aa = func_get_args();
            }
            if(is_array($aa)){
                $this->view = '`'.implode('`,`',$aa).'`';
            }else{
                $this->view = '`' . ((string)$aa) . '`';
            }
            return $this;
        }else{
            return $this->view;
        }
    }
    public function table() {
        if($n = func_num_args()){
            
            if($n == 1){
                $aa = func_get_arg(0);
            }else{
                $aa = func_get_args();
            }
            if(is_array($aa)){
                $this->table = '`'.implode('`,`',$aa).'`';
            }else{
                $this->table = '`' . ((string)$aa) . '`';
            }
            return $this;
        }else{
            return $this->table;
        }
    }
    public function temporary() {
        if($n = func_num_args()){
            $a = func_get_arg(0);
            if(is_bool($a)){
                $this->temporary = $a;
            }else{
                $this->temporary = true;
                if(is_array($a)){
                    $this->table($a);
                }else{
                    $this->table(func_get_args());
                }
            }
            return $this;
        }else{
            return $this->temporary;
        }
    }
    public function event() {
        if(func_num_args()){
            $value = (string)func_get_arg(0);
            $vu = strtoupper($value);
            if(in_array($vu,array('INSERT','UPDATE','DELETE'))){
                $this->triggerEvent = $vu;
            }else{
                $this->event = $value;
            }
            return $this;
        }else{
            return $this->event;
        }
    }
    public function predicate($predicate) {
        $predicate = strtoupper($predicate);
        if(in_array($predicate,array('CASCADE','RESTRICT'))){
            $this->mode = $predicate;
        }elseif($predicate =='TEMPORARY'){
            $this->temporary = true;
        }else{
            $this->predicate =$predicate;
        }
        return $this;
    }
    public function returns($returnType) {
        $returnType = strtoupper($returnType);
        if(in_array($returnType,array('STRING','INTEGER','REAL','DECIMAL'))){
            $this->returns = $returnType;
        }
        return $this;
    }
    public function security($value) {
        $value = strtoupper($value);
        if(in_array($value,array('DEFINER','INVOKER'))){
            $this->security = $value;
        }
        return $this;
    }
    public function indexType($value) {
        $value = strtoupper($value);
        if(in_array($value,array('BTREE','HASH'))){
            $this->indexType = $value;
        }
        return $this;
    }
    public function wait($value=true) {
        $value = (bool)$value;
        $this->options()->add('WAIT',$value);
        return $this;
    }
    public function rename($new_name) {
        $this->options()->add('RENAME',"`$new_name`");
        return $this;
    }
    public function ignore($value=true) {
        $this->ignore = (bool)$value;
        return $this;
    }
    public function replace($value=true) {
        $this->replace = (bool)$value;
        return $this;
    }
    public function time($value) {
        $value = strtoupper($value);
        if(in_array($value,array('BEFORE','AFTER'))){
            $this->time = $value;
        }
        return $this;
    }
    public function triggerEvent($value) {
        $value = strtoupper($value);
        if(in_array($value,array('INSERT','UPDATE','DELETE'))){
            $this->triggerEvent = $value;
        }
        return $this;
    }
    public function mode($mode) {
        $this->mode = strtoupper($mode);
        return $this;
    }
    
    public function ifExists() {
        $this->predicate = 'IF EXISTS';
        return $this;
    }
    public function ifNotExists() {
        $this->predicate = 'IF NOT EXISTS';
        return $this;
    }
    public function cascade() {
        $this->mode = 'CASCADE';
        return $this;
    }
    public function restrict() {
        $this->mode = 'RESTRICT';
        return $this;
    }
    public function options() {
        if(!($this->options instanceof characteristics)) $this->options = new characteristics;
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
        if(!($this->parameters instanceof parameters)) $this->parameters = new parameters;
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
        if(!($this->partition instanceof partition)) $this->partition = new partition;
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
    public function fields() {
        if(!($this->fields instanceof ddl_fields)) $this->fields = new ddl_fields;
        if(func_num_args()){
            if(func_num_args()==1){
                $this->fields->addColumn(func_get_arg(0));
            }else{
                foreach(func_get_args() as $f)
                    $this->fields->addColumn($f);
            }
            return $this;
        }else{
            return $this->fields;
        }
    }
    public function addColumn($name, $options=array()) {
        $f = $this->fields()->add($name, $options);
        $f->mode = 'ADD';
        $f->type = 'COLUMN';
        return $f;
    }
    public function addAutoIncrement($name, $options=array()) {
        $options['auto_increment'] = true;
        $options['data_type'] = 'int';
        $f = $this->fields()->add($name, $options);
        $f->mode = 'ADD';
        $f->type = 'COLUMN';
        return $f;
    }
    
    public function addGeneratedColumn($name,$expr, $options=array()) {
        $f = $this->fields()->add($name, $options);
        $field->mode = 'ADD';
        $field->type = "COLUMN";
        $field->generated = true;
        $field->expr = $expr;
        $this->data[] = $field;
        return $field;
    }
    public function addIndex($name, $options=array()) {
        $f = $this->fields()->add($name, $options);
        $f->mode = 'ADD';
        $f->type = 'INDEX';
        return $f;
    }
    public function dropIndex($name) {
        $f = $this->fields()->add($name);
        $f->mode = 'DROP';
        $f->type = 'INDEX';
        return $f;
    }
    public function dropColumn($name) {
        $f = $this->fields()->add($name);
        $f->mode = 'DROP';
        $f->type = 'COLUMN';
        return $f;
    }
    public function changeColumn($name, $newName, $options=array()) {
        $f = $this->fields()->add($name, $options);
        $f->mode = 'CHANGE';
        $f->type = 'COLUMN';
        $f->new_name =$newName;
        return $f;
    }
    public function modifyColumn($name, $options=array()) {
        $f = $this->fields()->add($name, $options);
        $f->mode = 'MODIFY';
        $f->type = 'COLUMN';
        return $f;
    }
    public function alterColumn($name, $default) {
        $f = $this->fields()->add($name, array());
        $f->mode = 'ALTER';
        $f->type = 'COLUMN';
        $f->default =$default;
        return $f;
    }
    public function convert_charset($charset) {
        if($charset){
            $this->options()->add('CONVERT TO CHARACTER SET', $charset);
        }else{
            $this->options()->delete('CONVERT TO CHARACTER SET');
        }
        return $this;
    }
    
    public function sql() {
        $parts =array();
        $charset = $collation = $engine = '';
        if((NULL !==$this->options)){
            $engine = $this->options->get('engine');
            $collation = $this->options->get('collation');
            $charset = $this->options->get('charset');
        }
        $add_post_field_comma = false;
        
        switch($this->type){
            
        case DML_CREATE:
        case DML_ALTER:
            if($this->type == DML_ALTER){
                $is_create = false;
                $parts[] = 'ALTER';
            }else{
                $is_create = true;
                $parts[] = 'CREATE';
            }
            if($this->database){
                $parts[] = 'DATABASE';
                if($is_create && in_array($this->predicate,array('IF NOT EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->database`";
                //TODO with ALTER the dbname can be omitted but this LOGIC does not allow that
                if($charset) $parts[] = 'DEFAULT CHARACTER SET = ' . $charset;
                if($collation) $parts[] = 'DEFAULT COLLATE = ' . $collation;
            }elseif($this->function || $this->procedure){
                if($is_create){
                    if($this->definer && !$this->soname){
                        $parts[] = "DEFINER = $this->definer";
                    }else{
                        if(in_array($this->predicate,array('AGGREGATE'))) $parts[] = $this->predicate;;
                    }
                }
                
                if($this->function){
                    $parts[] = 'function';
                    $parts[] = "`$this->function`";
                }else{
                    $parts[] = 'PROCEDURE';
                    $parts[] = "`$this->procedure`";
                }
                if($is_create){
                    if(!$this->soname){
                        if((NULL !==$this->parameters)){
                            $w = $this->parameters->raw();
                            $parts[] = "($w)";
                        }else{
                            $parts[] = "()";
                        }
                    }
                    if($this->returns){
                        $parts[] = 'RETURNS ' . $this->returns;
                    }
                    if($this->soname){
                        $parts[] = "SONAME '$this->soname'";
                    }else{
                        if((NULL !==$this->options)){
                            //TODO try to accomodate that only one of
                            //       { CONTAINS SQL | NO SQL | READS SQL DATA | MODIFIES SQL DATA }
                            if($f =$this->options->raw()) $parts[] = "{$f}";
                        }
                        if($this->body) $parts[] = "BEGIN $this->body END";
                    }
                }
                if((NULL !==$this->options)){
                    //TODO try to accomodate that only one of
                    //       { CONTAINS SQL | NO SQL | READS SQL DATA | MODIFIES SQL DATA }
                    if($f =$this->options->raw()) $parts[] = "{$f}";
                }
                
            }elseif($this->event){
                #MUST HAPPEN before table
                if($this->definer){
                    $parts[] = "DEFINER = $this->definer";
                }
                $parts[] = 'EVENT';
                if($is_create && in_array($this->predicate,array('IF NOT EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->event`";
                if((NULL !==$this->options)){
                    //TODO try to accomodate that only one of
                    //       [ENABLE | DISABLE | DISABLE ON SLAVE]
                    if($f =$this->options->raw()) $parts[] = "{$f}";
                }
                if($this->body) $parts[] = "DO BEGIN $this->body END";
            }elseif($this->logfile){
                $parts[] = 'LOGFILE GROUP';
                $parts[] = "`$this->logfile`";
                if((NULL !==$this->options)){
                    
                    /* ALTER: ADD UNDOFILE 'file_name'    [INITIAL_SIZE [=] size]    [WAIT]*/
                    /* CREATE: ADD UNDOFILE 'undo_file'    [INITIAL_SIZE [=] initial_size] [UNDO_BUFFER_SIZE [=] undo_buffer_size]    [REDO_BUFFER_SIZE [=] redo_buffer_size] [NODEGROUP [=] nodegroup_id]     [WAIT]*/
                    if($f =$this->options->raw()) $parts[] = "{$f}";
                }
                if($engine) $parts[] = "ENGINE $engine";
                
            }elseif($this->trigger){
                //CREATE ONLY
                if($this->definer){
                    $parts[] = "DEFINER = $this->definer";
                }
                $parts[] = 'TRIGGER';
                $parts[] = "`$this->trigger`";
                if(in_array($this->time,array('BEFORE','AFTER'))) $parts[] = $this->time;;
                if(in_array($this->triggerEvent,array('INSERT','UPDATE','DELETE'))) $parts[] = $this->triggerEvent;;
                if($this->table) $parts[] = 'ON ' . $this->table.' FOR EACH ROW';
                if($this->follows){
                    $parts[] = "FOLLOWS $this->follows";
                }elseif($this->preceeds){
                    $parts[] = "PRECEDES $this->preceeds";
                }
                if($this->body) $parts[] = "BEGIN $this->body END";
            }elseif($this->tablespace){
                $parts[] = 'TABLESPACE';
                $parts[] = "`$this->tablespace`";
                if((NULL !==$this->options)){
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
                    if($f =$this->options->raw()) $parts[] = "{$f}";
                }
                if($engine) $parts[] = "ENGINE = $engine";
                
            }elseif($this->server){
                $parts[] = 'SERVER';
                $parts[] = "`$this->server`";
                if($is_create && $this->wrapper) $parts[] = "FOREIGN DATA WRAPPER '$this->wrapper'";
                if((NULL !==$this->options)){
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
                if(in_array($this->predicate,array('UNIQUE','FULLTEXT','SPATIAL'))) $parts[] = $this->predicate;;
                $parts[] = 'INDEX';
                $parts[] = "`$this->index`";
                $parts[] = 'ON';
                $parts[] = "$this->table";
                
                //TODO ?(col_name [(length)] [ASC | DESC])
                if((NULL !==$this->fields)){
                    if($f =$this->fields->asColumnlistDefinitions()->toString()) $parts[] = "({$f})";
                }
                if($this->indexType) $parts[] = 'USING ' . $this->indexType;;
                if((NULL !==$this->options)){
                    /*
                    KEY_BLOCK_SIZE [=] value
                      | index_type
                      | WITH PARSER parser_name
                    */
                    if($f =$this->options->raw()) $parts[] = "{$f}";
                }
                if($this->algorithm){
                    if(in_array($this->algorithm,array('DEFAULT','INPLACE','COPY'))) $parts[] = 'ALGORITHM = ' . $this->algorithm;;
                }elseif($this->lock){
                    if(in_array($this->lock,array('DEFAULT','NONE','SHARED','EXCLUSIVE'))) $parts[] = 'LOCK = ' . $this->lock;;
                }
            }elseif($this->view){
                if($is_create && $this->predicate){
                    if(in_array($this->predicate,array('REPLACE','OR REPLACE','IF EXISTS'))) $parts[] = 'OR REPLACE';
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
                $parts[] = "$this->view";
                if((NULL !==$this->fields)){
                    if($f =$this->fields->asColumnlistDefinitions()->toString()) $parts[] = "({$f})";
                }
                if($this->select){
                    $parts[] = "AS $this->select";
                }
                
                if(NULL !== $this->with){
                    $parts[] = 'WITH';
                    if(in_array($this->with,array('CASCADED','LOCAL'))) $parts[] = $this->with;;
                    $parts[] = 'CHECK OPTION';
                }
            }
            elseif($this->table){
                if($is_create){
                    if($this->temporary) $parts[] = 'TEMPORARY';
                }else{
                    if($this->ignore) $parts[] = 'IGNORE';
                    elseif(in_array($this->predicate,array('IGNORE'))) $parts[] = $this->predicate;;
                }
                
                $parts[] = 'TABLE';
                if($is_create && in_array($this->predicate,array('IF NOT EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "$this->table";
                if($is_create){
                    if($this->like){
                        $parts[] = "(LIKE  $this->like)";
                    }else{
                        if((NULL !==$this->fields)){
                            if($f =$this->fields->asCreateDefinitions()->toString()) $parts[] = "({$f})";
                        }
                        if((NULL !==$this->options)){
                            if($f =$this->options->raw()) $parts[] = "{$f}";
                        }
                        if((NULL !==$this->partition)){
                            if($f =$this->partition->raw()) $parts[] = "{$f}";
                        }
                        if($this->ignore) $parts[] = 'IGNORE';
                        elseif($this->replace) $parts[] = 'REPLACE';
                        elseif(in_array($this->mode,array('IGNORE','REPLACE'))) $parts[] = $this->mode;
                        if($this->select){
                            $parts[] = "AS  $this->select";
                        }
                    }
                }else{
                    if((NULL !==$this->fields)){
                        if($f =$this->fields->asAlterDefinitions()->toString()){
                            $parts[] = "{$f}";
                            $add_post_field_comma = true;
                        }
                    }
                    if((NULL !==$this->options)){
                        if($f =$this->options->raw()){
                            if($add_post_field_comma){
                                $parts[] = ",";
                            }
                            $parts[] = "{$f}";
                            $add_post_field_comma = false;
                        }
                    }
                    if((NULL !==$this->partition)){
                        if($f =$this->partition->raw()){
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
        case DML_TRUNCATE:
            $parts[] = 'TRUNCATE';
            if($this->table){
                $parts[] = 'TABLE';
                $parts[] = "$this->table";
            }
        break;
        case DML_DROP:
            $parts[] = 'DROP';
            if($this->index){
                $parts[] = 'INDEX';
                $parts[] = "`$this->index`";
                $parts[] = 'ON';
                $parts[] = "$this->table";
                if($this->algorithm){
                    if(in_array($this->algorithm,array('DEFAULT','INPLACE','COPY'))) $parts[] = 'ALGORITHM = ' . $this->algorithm;;
                }elseif($this->lock){
                    if(in_array($this->lock,array('DEFAULT','NONE','SHARED','EXCLUSIVE'))) $parts[] = 'LOCK = ' . $this->lock;;
                }
            }elseif($this->table){
                if($this->temporary) $parts[] = 'TEMPORARY';
                $parts[] = 'TABLE';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "$this->table";
                if(in_array($this->mode,array('CASCADE','RESTRICT'))) $parts[] = $this->mode;;
            }elseif($this->view){
                $parts[] = 'VIEW';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "$this->view";
                if(in_array($this->mode,array('CASCADE','RESTRICT'))) $parts[] = $this->mode;;
            }elseif($this->event){
                $parts[] = 'EVENT';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->event`";
            }elseif($this->tablespace){
                $parts[] = 'TABLESPACE';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->tablespace`";
                if($engine) $parts[] = 'ENGINE = ' . $engine;
            }elseif($this->logfile){
                $parts[] = 'LOGFILE GROUP';
                $parts[] = "$this->logfile";
                if($engine) $parts[] = 'ENGINE = ' . $engine;
            }elseif($this->database){
                $parts[] = 'DATABASE';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->database`";
            }elseif($this->server){
                $parts[] = 'SERVER';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->server`";
            }elseif($this->function){
                $parts[] = 'function';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->function`";
            }elseif($this->procedure){
                $parts[] = 'PROCEDURE';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->procedure`";
            }elseif($this->trigger){
                $parts[] = 'TRIGGER';
                if(in_array($this->predicate,array('IF EXISTS'))) $parts[] = $this->predicate;;
                $parts[] = "`$this->trigger`";
            }
            
        break;
        }
        
        return implode(' ', $parts);
    }
    public function hasDefinitions() {
        switch($this->type){
        case DML_CREATE:
        case DML_ALTER:
            if($this->type == DML_ALTER){
                $is_create = false;
            }else{
                $is_create = true;
            }
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
                if((NULL !==$this->options)){
                    if($f =$this->options->raw()){
                        return true;
                    }
                }
                
            }elseif($this->event){
                if($this->body){
                    return true;
                }
            }elseif($this->logfile){
                if((NULL !==$this->options)){
                    if($f =$this->options->raw()){
                        return true;
                    }
                }
            }elseif($this->trigger){
                if($this->body){
                    return true;
                }
            }elseif($this->tablespace){
                if((NULL !==$this->options)){
                    if($f =$this->options->raw()){
                        return true;
                    }
                }
            }elseif($this->server){
                if((NULL !==$this->options)){
                    return true;
                }
            }elseif($this->index){
                if((NULL !==$this->fields)){
                    if($f =$this->fields->asColumnlistDefinitions()->toString()){
                        return true;
                    }
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
                        if((NULL !==$this->fields)){
                            if($f =$this->fields->asCreateDefinitions()->toString()){
                                return true;
                            }
                        }
                        if((NULL !==$this->options)){
                            if($f =$this->options->raw()){
                                return true;
                            }
                        }
                        if((NULL !==$this->partition)){
                            if($f =$this->partition->raw()){
                                return true;
                            }
                        }
                        if($this->select){
                            return true;
                        }
                    }
                }else{
                    if((NULL !==$this->fields)){
                        if($f =$this->fields->asAlterDefinitions()->toString()){
                            return true;
                        }
                    }
                    if((NULL !==$this->options)){
                        if($f =$this->options->raw()){
                            return true;
                        }
                    }
                    if((NULL !==$this->partition)){
                        if($f =$this->partition->raw()){
                            return true;
                        }
                    }
                }
            }
        
        break;
        case DML_TRUNCATE:
            if($this->table){
                 return true;
            }
        break;
        case DML_DROP:
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
    
}

class characteristics{
    
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
  
    private $data =array();
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function add($name,$value=true){
        $name = strtoupper($name);
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
    public function raw() {
        $f=array();
        foreach($this->data as $name => $value){
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
    public function toArray() {
        return $this->data;
    }
    public function __toString() {
        return $this->raw();
    }

    
}
class parameters{
    private $data =array();
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function add($name,$type,$access='IN'){
        if(!$name) return $this;
        $key = strtoupper($name);
        $access = strtoupper($access);
        if(!in_array($access,array('IN','OUT','INOUT'))) $access ='';
        $this->data[$key] = trim("{$access} $name $type");
        return $this;
    }
    public function delete($name){
        $key = strtoupper($name);
        unset($this->data[$key]);
        return $this;
    }
    public function toArray() {
        return $this->data;
    }
    public function raw() {
        $f=array();
        foreach($this->data as $name => $value){
            $f[] = "$value";
        }
        return implode(', ', $f);
    }
    public function __toString() {
        return $this->raw();
    }
}

class create_definition{
    
}
class partition{
    private $data =array();
    public function clear() {
        $this->data= array();
        return $this;
    }
    public function add($name,$type,$access='IN'){
        if(!$name) return $this;
        $key = strtoupper($name);
        $access = strtoupper($access);
        if(!in_array($access,array('IN','OUT','INOUT'))) $access ='';
        $this->data[$key] = trim("{$access} $name $type");
        return $this;
    }
    public function delete($name){
        $key = strtoupper($name);
        unset($this->data[$key]);
        return $this;
    }
    public function toArray() {
        return $this->data;
    }
    public function raw() {
        $f=array();
        foreach($this->data as $name => $value){
            $f[] = "$value";
        }
        return implode(', ', $f);
    }
    public function __toString() {
        return $this->raw();
    }
    /*{ [LINEAR] HASH(expr)
        | [LINEAR] KEY [ALGORITHM={1|2}] (column_list)
        | RANGE{(expr) | COLUMNS(column_list)}
        | LIST{(expr) | COLUMNS(column_list)} }*/
    public function by($value){
        $this->data['PARTITION BY'] = $value;
        return $this;
    }
    public function partitions($value){
        $this->data['PARTITIONS'] = (int)$value;
        return $this;
    }
    /*[SUBPARTITION BY
        { [LINEAR] HASH(expr)
        | [LINEAR] KEY [ALGORITHM={1|2}] (column_list) }
      [SUBPARTITIONS num]
    ]*/
    public function subpartitionby($value){
        $this->data['SUBPARTITION BY'] = $value;
        return $this;
    }
    public function subpartitions($value){
        $this->data['SUBPARTITIONS'] = (int)$value;
        return $this;
    }
}
/*
partition_options:
     
    
    [(partition_definition [, partition_definition] ...)]

partition_definition:
    PARTITION partition_name
        [VALUES 
            {LESS THAN {(expr | value_list) | MAXVALUE} 
            | 
            IN (value_list)}]
        [[STORAGE] ENGINE [=] engine_name]
        [COMMENT [=] 'comment_text' ]
        [DATA DIRECTORY [=] 'data_dir']
        [INDEX DIRECTORY [=] 'index_dir']
        [MAX_ROWS [=] max_number_of_rows]
        [MIN_ROWS [=] min_number_of_rows]
        [TABLESPACE [=] tablespace_name] 
        [(subpartition_definition [, subpartition_definition] ...)]

subpartition_definition:
    SUBPARTITION logical_name
        [[STORAGE] ENGINE [=] engine_name]
        [COMMENT [=] 'comment_text' ]
        [DATA DIRECTORY [=] 'data_dir']
        [INDEX DIRECTORY [=] 'index_dir']
        [MAX_ROWS [=] max_number_of_rows]
        [MIN_ROWS [=] min_number_of_rows]
        [TABLESPACE [=] tablespace_name]*/ 