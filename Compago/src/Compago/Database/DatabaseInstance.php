<?php

namespace Compago\Database;


class DatabaseInstance extends \MySQLi
{
    
    private $query='';
    private $connected=false;
    
    public function __destruct() {
        @$this->close();
    }
    public function __get($name) {
        if($name == 'connect_error'){
            //the property of mysqli does not work properly before PHP 5.2.9
            return mysqli_connect_error($this);
        }
        if(property_exists($this,$name)){
            return $this->name;
        }
    }
    public function __construct($hostname=null, $username=null, $password=null, $database=null) {
        
        if(func_num_args() ==0){
            parent::init();
            //real_connect($host, $user, $pass, $db)
            //must be called to make ready
        }else{
            @parent::__construct($hostname, $username, $password, $database);
            $this->connected = $this->connect_errno ==0; 
            if($this->connect_errno) {
                throw new ConnectionError('Connect Error (' . $this->connect_errno . ') '. $this->connect_error, 1); 
            }
        }
        
    }
    public function real_connect($hostname=null, $username=null, $password=null, $database=null,$port=null,$socket=null,$flags=null){
        parent::real_connect($hostname, $username, $password, $database,$port,$socket,$flags);
        $this->connected = $this->connect_errno ==0; 
        if($this->connect_errno) {
            throw new ConnectionError('Connect Error (' . $this->connect_errno . ') '. $this->connect_error, 1); 
        }
        return $this;
    }
    public function connect($hostname=null, $username=null, $password=null, $database=null,$port=null,$socket=null,$flags=null){
        parent::real_connect($hostname, $username, $password, $database,$port,$socket,$flags);
        $this->connected = $this->connect_errno ==0; 
        if($this->connect_errno) {
            throw new ConnectionError('Connect Error (' . $this->connect_errno . ') '. $this->connect_error, 1); 
        }
        return $this;
    }
    public function migrate($table, $callback){
        //$EXISTING = $this->getSchemaDefinition($table);
        /*if ($EXISTING->hasColumns()){
            $NEWDEFSQL = EXSQL::alter($table);
        } else {
            $NEWDEFSQL = EXSQL::create($table);
        }*/
        if (is_callable($callback)){
            $EXISTING = $this->getSchemaDefinition($table);
            $SM = new \Compago\Database\Blueprint\Blueprint($table,$EXISTING);
            $callback($SM,$EXISTING);
            //__Er($SM);
            if ($SM->hasDefinitions()){
                $this->execute($sql = $SM->getSql());
                //__Er($SM);
                //__Er($sql);
            }
        }
    }
/*888888888888888888888888888888888888888888888

BRINGING properties to function BASED access

888888888888888888888888888888888888888888888888*/

    public function affected(){return $this->affected_rows;}
    public function affected_rows(){return $this->affected_rows;}
    public function client_info(){return $this->client_info;}
    public function client_version(){return $this->client_version;}
    public function get_client_version(){return $this->client_version;}
    public function connected(){return $this->connected;}
    public function connect_errno(){return $this->connect_errno;}
    public function connect_error(){
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            return $this->connect_error;
        }
        return mysqli_connect_error($this);
    }
    public function errno(){return $this->errno;}
    public function error(){return $this->error;}
    public function error_list(){return $this->error_list;}
    public function error_message(){
        if($this->errno)
            return sprintf('#%-u (%s) %s; %s', $this->errno,$this->sqlstate, $this->error, $this->info);
        else
            return '';
    }
    public function field_count(){return $this->field_count;}
    public function host_info(){return $this->host_info;}
    public function protocol_version(){return $this->protocol_version;}
    public function server_info(){return $this->server_info;}
    public function server_version(){return $this->server_version;}
    public function info(){return $this->info;}
    public function insert_id(){return $this->insert_id;}
    /*public function insert_id()
    {
        if($rs = parent::query("SELECT LAST_INSERT_ID() as id",MYSQLI_USE_RESULT))
        {
            if($row = $rs->fetch_assoc()){
                return $row['id'];
            }
            $rs->close();
        }
        return false;
    }*/
    public function sqlstate(){return $this->sqlstate;}
    public function thread_id(){return $this->thread_id;}
    public function warning_count(){return $this->warning_count;}
    
    public function get_client_stats(){
        if(function_exists('mysqli_get_client_stats'))
        {
            return mysqli_get_client_stats();
        }
        return array();
    }
    public function client_stats(){
        return $this->get_client_stats();
    }
    public function get_connection_stats(){
        if(function_exists('get_connection_stats '))
        {
            return parent::get_connection_stats();
        }else{
            try{
                $array = array('_NOT_USING_MYSQL_ND'=>1);
                $result = parent::query('SHOW SESSION STATUS;', MYSQLI_USE_RESULT); 
                while ($row = $result->fetch_assoc()) { 
                    $array[strtolower($row['Variable_name'])] = $row['Value']; 
                } 
                $result->close();
                return $array; 
            }catch(\exception $e){
                
            }
        }
        return array();
    }
    public function connection_stats(){
        return $this->get_connection_stats();
    }

    
/*888888888888888888888888888888888888888888888

DEPREACTED public functionS

888888888888888888888888888888888888888888888888*/
    public $prefix='';
    public function setPrefix($prefix){
        $this->prefix = $prefix;
    }
    /**
     * Converts short table name {tablename} to real table name
     * @param string sql
     * @return string sql
     */
    public function fixTableNames($sql) {
        return preg_replace('/\{([a-z][a-z0-9_]*)\}/',$this->prefix.'$1', $sql);
    }    
/*888888888888888888888888888888888888888888888

GENERAL public functionS

888888888888888888888888888888888888888888888888*/
    protected $tz_once;
    public function configTimeZone($val=null)
    {
        if($this->tz_once) return ;
        $this->tz_once = true; 
        if(empty($val))
        {
            $d = new \DateTime();
            $temp = $d->getOffset();
            $sgn = ($temp < 0 ? -1 : 1);
            $temp = abs($temp);
            $h = floor($temp / 3600);
            $m = ($temp % 3600) / 60;
            $val =sprintf('%+d:%02d',$h*$sgn,$m);
        }
        $this->execute("SET time_zone = '$val'");
        if($this->errno() == 1298 ){//unknown zone
            $d = new \DateTime();
            $temp = $d->getOffset();
            $sgn = ($temp < 0) ? -1 : 1;
            $temp = abs($temp);
            $h = floor($temp / 3600);
            $m = ($temp % 3600) / 60;
            $nval =sprintf('%+d:%02d',$h*$sgn,$m);
            if($nval != $val)$this->execute("SET time_zone = '$nval'");
        }
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
        $this->set_type($type);
        return $w;
    }
    public static function getSQLType($str){
          $str=trim($str);
          return strtoupper(substr($str,0,strpos($str,' ')));      
    }
    
    public function getSchemaDefinition($table,$dbname='') {
        if($table){
            $TBL = new Blueprint\Table();
            try{
                $table = $this->escape($table);
                
                if($dbname){
                    $dbname = $this->escape($dbname);
                    $dbwh = "AND `table_schema` = '$dbname'";
                    $fqname = "`{$dbname}`.`$table`";
                }else{
                    $dbwh = 'AND `table_schema` = DATABASE()';
                    $fqname = "`$table`";
                }
                $found = false;
                
                $q = $this->query("SHOW FULL FIELDS FROM $fqname");
                $column_index = 0;
                $previous_column_name = '';
                while($row = $q->row()){
                    $name = $row['Field'];
                    $row['column_index'] = $column_index;
                    $row['previous_column_name'] = $previous_column_name;
                    $row['data_type'] = $row['Type'];
                    unset($row['Type'],$row['Field']);
                    $TBL->addColumn($name,$row);
                    $previous_column_name = $name;
                    $column_index++;
                    $found = true;
                }
                    
                if($found){
                    $TBL->setName($table);
                    $q = $this->query("SHOW KEYS FROM $fqname");
                    while($row = $q->row()){
                        $row['extra'] = $row['Comment'];
                        $row['comment'] = $row['Index_comment'];
                        unset($row['Index_comment']);
                        $TBL->addIndex($row['Key_name'],$row);
                        $found = true;
                    }
                    $q = $this->query("SELECT * FROM information_schema.tables WHERE `table_name` = '$name' {$dbwh}");
                    if($row = $q->row()){
                        $TBL->setTableOptions($row);
                    }
                }
            }catch(\Exception $e){
                error_log($e->getMessage());
            }
            return $TBL;
        }else{
            return new Blueprint\Table();
        }
    }



/*888888888888888888888888888888888888888888888

SQL public functionS

888888888888888888888888888888888888888888888888*/
    public function alter($table,$statements ){
        if(func_num_args()>2){
            $statements = func_get_args();
            array_unshift($statements);
        }
        if(is_array($statements))   $statements= implode(',',array_filter($statements));
        
        if(!$table) return 0;
        if(!$statements) return 0;
        
        $query = "ALTER TABLE $table $statements";
        return $this->execute($query);
        
    }
    public function createIndex($indexName, $table, $fields, $unique=false){
        if(is_array($fields))   $fields= implode(',',array_filter($fields));
        
        if(!$indexName) return 0;
        if(!$table) return 0;
        if(!$fields) return 0;
        
        $query = $unique?'CREATE UNIQUE INDEX':'CREATE INDEX';
        $query .= " $indexName ON $table ($fields)";
        $query = $this->fixTableNames($query);
        return $this->execute($query);
        
    }
    public function createTable($table,$statements ){
        if(func_num_args()>2){
            $statements = func_get_args();
            array_unshift($statements);
        }
        if(is_array($statements))   $statements= implode(',',array_filter($statements));
        
        if(!$table) return 0;
        if(!$statements) return 0;
        
        $query = "CREATE TABLE $table ($statements)";
        return $this->execute($query);
    }
    /**
     * DBO::delete()
     * 
     * @param mixed $table
     * @param mixed $where
     * @return
     */
    public function delete($table,$where,$limit=0,$order=''){
        if(is_array($table))  $table= implode(',',array_filter($table));
        if(is_array($where))  $where= implode(' AND ',array_filter($where)); 
        if(is_array($limit))  $limit= implode(',',$limit); 
        if(is_array($order))  $order= implode(',',array_filter($order));
        
        if(!$table) return 0;
        
        $query = "DELETE FROM $table";
        if($where)  $query.=" WHERE $where";
        if($order)  $query.=" ORDER BY $order";
        if($limit)  $query.=" LIMIT $limit";
        
        return $this->execute($query);
    }
    public function dropIndex($indexName, $table){
        if(!$table) return 0;
        if(!$indexName) return 0;
        
        $query = "ALTER TABLE $table DROP INDEX $indexName";
        $query = $this->fixTableNames($query);
        return $this->execute($query);
    }
    
    public function dropTable($table){
        if(!$table) return 0;
        
        $query = "DROP TABLE $table";
        return $this->execute($query);
    }
    public function escape($array)
    {
        if(!$this->connected()){
            throw new ConnectionError('Connection is not open. Cannot use escape()', 1);
        }
        if($array === null){
            return $array;
        }
        if(!is_array($array))
        {
            return $this->real_escape_string(trim($array));
        }
        foreach($array as $k => $v)
        {
            if(is_array($v)){
                $array[$k] = $this->escape($v);
            }else if ( $v !== null){
                $array[$k] = $this->real_escape_string(trim($v));
            }
        }
        return $array;
    }
    /**
     * DBO::execute()
     * 
     * @param mixed $query
     * @return resource id
     */
    public function execute($query){
        if(func_num_args()>1){
            $numParams = func_num_args(); 
            $params = func_get_args(); 
            
            for ($i = 1; $i < $numParams; $i++){
                $params[$i] = $this->escape($params[$i]); 
            }
            $query = call_user_func_array('sprintf', $params); 
        }
        
        $query = $this->fixTableNames($query);
        $this->query = $query;
        $result = parent::real_query($query );
        if($result){
            if($this->field_count > 0){
                $obj= new Result($this,$query);
                return $obj;
            }
        }
        return $result;
    }
    /*public function xquery($query,$qmode = MYSQLI_STORE_RESULT){
        return parent::query($query,  $qmode);
    }*/
    public function exists($table, $where=''){
        if(func_num_args()>1){
            if(is_array($table)) $table= implode(', ',array_filter($table));
            if(is_array($where)) $where= implode(' AND ',array_filter($where));
            
            if(stripos($where,'like')){
                $query = "SELECT * FROM $table WHERE $where LIMIT 1";
                if($rs = $this->execute($query)){
                    $r =  $rs->num_rows;
                    $rs->free();
                    return $r;
                }
                return false;
            }else{
                $query = "SELECT * FROM $table";
                if($where)  $query.=" WHERE $where";
                $query = "SELECT EXISTS($query) as c";
                if($rs = $this->execute($query)){
                    $r = $rs->fetch_array();
                    $rs->free();
                    return $r[0];
                }
                return false;
            }
            
        }else if(stripos($table,' ')===false){
            if($rs = $this->execute("SHOW TABLES LIKE '$table'")){
                $r =  $rs->num_rows;
                $rs->free();
                return $r>0;
            }
            return false;
        }else{
            //for compatibility with deprecated : public function exists($query)
            $query = $table;
            if(!stripos(substr($query,-20),'limit'))
            {
                $query .= ' LIMIT 1';
            }
            if($rs = $this->execute($query)){
                $r =  $rs->num_rows;
                $rs->free();
                return $r;
            }
            return false;
        }
    }
    public function flush_multi_queries(){
          while ($this->more_results() && $this->next_result());
    }
    /**
     * DBO::insert()
     * 
     * @param mixed $table
     * @param string $set or array $set
     * @param string $onDuplicate or array $onDuplicate
     * @param bool $ignore
     * @return
     */
    public function insert($table,$set, $onDuplicate='', $ignore=false){
        if(is_array($table)) $table= implode(',',array_filter($table));
        if(is_array($set))   $set= implode(',',array_filter($set));
        if(is_array($onDuplicate)) $onDuplicate= implode(',',array_filter($onDuplicate));
        
        if(!$set) return 0;
        if($ignore==2 || strtoupper($ignore)=='DELAYED'){
            $query = 'INSERT DELAYED INTO';
        }else{
            $query = $ignore?'INSERT IGNORE INTO':'INSERT INTO';
        }
        
        $query .= " $table SET $set";
        if($onDuplicate)  $query.=" ON DUPLICATE KEY UPDATE $onDuplicate";
        return $this->execute($query);
    }
    public function keys_disable($table ){
        if(!$table) return 0;
        
        $query = "ALTER TABLE $table DISABLE KEYS";
        return $this->execute($query);
    }
    public function keys_enable($table ){
        if(!$table) return 0;
        
        $query = "ALTER TABLE $table ENABLE KEYS";
        return $this->execute($query);
    }
    /**
     * DBO::kill_query()
     * @param string $processlist_id
     *        the Id column of SHOW PROCESSLIST output,
     * @return
     */
    public function kill_query($processlist_id){
        if(!$processlist_id) return;
        return $this->execute("KILL QUERY $processlist_id");
    }
    public function multi_query($query)
    {
        if(is_array($query)) $query = implode(';',$query);
        $rs = parent::multi_query($query);
        return new MultiResult($this,$query,$rs);
    }
    public function queryf($query, $params){
        $numParams = func_num_args(); 
        $params = func_get_args(); 
        
        for ($i = 1; $i < $numParams; $i++){
            $params[$i] = $this->escape($params[$i]); 
        }
        $query = call_user_func_array('sprintf', $params);
        return $this->query($query);
    }
    /**
     * DBO::query()
     * 
     * @param mixed $query  
     *      multiple parameters "SELECT * FROM a WHERE id = '%s' AND pw='%s' ", 'John', 'Pass'
     * @return Result() object
     * 
     * PHP 7 introduced an error
     * Declaration of  query($query = NULL) should be compatible with mysqli::query($query, $resultmode = NULL) in 
     */
    public function query($query=null, $php7err_relove=null)
    {
        if(func_num_args()==0){
            return $this->query;
        }
        $rs = $this->execute($query);
        if(is_bool($rs) || $rs === null){
            $a =array();
            $a['result'] = $rs;
            $a['query'] = $query;
            $a['affected_rows'] = $this->affected_rows();
            $a['field_count'] = $this->field_count();
            $a['sqlstate'] = $this->sqlstate();
            $a['errno'] = $this->errno;
            $a['error'] = $this->error;
            $a['warning_count'] = $this->warning_count();
            if($a['warning_count']){
                $a['warnings'] = $this->get_warnings();
            }
            if($rs === true){
                $a['insert_id'] = $this->insert_id;
            }
            return new PartialResult($a);
        }else{
            return $rs;
        }
         
    }
    public function replace($table,$statements ){
        if(is_array($table)) $table= implode(',',array_filter($table));
        if(func_num_args()>2){
            $statements = func_get_args();
            array_unshift($statements);
        }
        if(is_array($statements))   $statements= implode(',',array_filter($statements));
        
        if(!$table) return 0;
        if(!$statements) return 0;
        
        $query = "REPLACE INTO $table SET $statements";
        return $this->execute($query);
    }
    public function select($fields,$table='DUAL',$where='',$order='',$limit=0,$groupBy=''){
        if(is_array($table))   $table= implode(', ',array_filter($table));
        if(is_array($fields))  $fields= implode(',',array_filter($fields));
        if(is_array($order))   $order= implode(',',array_filter($order));
        if(is_array($limit))   $limit= implode(',',$limit);
        if(is_array($where))   $where= implode(' AND ',array_filter($where));
        if(is_array($groupBy)) $groupBy= implode(',',array_filter($groupBy));
        
        if(!$fields) $fields='*';
        
        $query = "SELECT $fields FROM $table";
        if($where)  $query.=" WHERE $where";
        if($groupBy)$query.=" GROUP BY $groupBy";
        if($order)  $query.=" ORDER BY $order";
        if($limit)  $query.=" LIMIT $limit";
        return $this->query($query);
    }
    public function start_transaction( $flags =null, string $name=null) {
        if(func_num_args()==1){
            if(is_string($flags)){
                $name = $flags;
                $flags = null;
            }
        }
		return $this->begin_transaction( $flags, $name);
	}
    public function begin_transaction( $flags =null, $name=null) {
        if(version_compare(PHP_VERSION, '5.5', '<')){
            return $this->autocommit(FALSE);
        }
		if(func_num_args()==1){
            if(is_string($flags)){
                $name = $flags;
                $flags = null;
            }
        }
        return parent::begin_transaction( $flags, $name);
	}
    public function commit( $flags =null, $name=null) {
        if(version_compare(PHP_VERSION, '5.5', '<')){
            return $this->commit();
        }
		if(func_num_args()==1){
            if(is_string($flags)){
                $name = $flags;
                $flags = null;
            }
        }
        return parent::commit( $flags, $name);
	}
    /**
     * DBO::update()
     * 
     * @param mixed $table
     * @param mixed $where
     * @return
     */
    public function update($table,$set, $where='',$limit=0,$order='',$prefix=''){
        if(is_array($table)) $table= implode(',',array_filter($table));
        if(is_array($set))   $set= implode(',',array_filter($set));
        if(is_array($limit)) $limit= implode(',',$limit);
        if(is_array($where)) $where= implode(' AND ',array_filter($where));
        if(is_array($order)) $order= implode(',',array_filter($order));
        
        if(!$set) return 0;
        
        $query = "UPDATE {$prefix} $table SET $set";
        if($where)  $query.=" WHERE $where";
        if($order)  $query.=" ORDER BY $order";
        if($limit)  $query.=" LIMIT $limit";
        return $this->execute($query);
    }
}