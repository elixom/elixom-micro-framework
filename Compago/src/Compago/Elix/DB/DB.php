<?php
/**
 * @author Edwards
 * @copyright 2016
 * @version 20171026
 */
/**
 * @author Edwards
 * @copyright 2016
 * @version 20171026
 * DB using DBI
 * 
 * CHANGES
 * 
 * 
 * 
 * DOES NOT IMPLEMENT
 *  - get_proto_info  USE protocol_version()
 * 
 */
include_once('DBO.php');

class DB extends \Compago\Database{
    static $prefix='';
    private static $link=null;
    static $query=null;
    
    public static function __callStatic($name, $arguments) {
        if(self::$link === null){
             self::connect();
        }
        if(method_exists(self::$link,$name)){
            return call_user_func_array(array(self::$link,$name),$arguments);
        }
    }
    public static function open($hostname=null, $username=null, $password=null, $database=null,$port=null,$socket=null,$flags=null){
        $n = func_num_args();
        if($n==0){
            return new \ELIX\DB\DBO();
        }
        if($n>4){
            $db = new \ELIX\DB\DBO();
            $db->real_connect($hostname, $username, $password, $database,$port,$socket,$flags);
            return $db;
        }
        return new \ELIX\DB\DBO($hostname, $username, $password, $database); 
    }
    public static function connect(){
        //this public function exists for compatibility with OLD static database and GLOBAL $CFG
        //it was formerly ::init
        if(self::$link === null){
            try{
                $port=$socket=$flags=null;
                if(func_num_args()){
                    $a = array_merge(func_get_args(),array_fill(0, 6, null));
                    list($hostname, $username, $password, $database,$port,$socket,$flags) = $a;
                }else{
                    GLOBAL $CFG;
                    $hostname = $CFG->dbhost;
                    $username = $CFG->dbuser;
                    $password = $CFG->dbpass;
                    $database = $CFG->dbname;
                    
                    if(isset($CFG->dbport)) $port = $CFG->dbport;
                    if(isset($CFG->dbsocket)) $socket = $CFG->dbsocket;
                    if(isset($CFG->dbflags)) $flags = (int)$CFG->dbflags;
                }
                
                $db = new \ELIX\DB\DBO();
                $db->real_connect($hostname, $username, $password, $database,$port,$socket,$flags);
                if(!$db->errno()){
                    self::$link = $db;
                    $db->set_charset('utf8');
                    if(!empty($CFG->dbprefix)){
                        $db->setPrefix($CFG->dbprefix);
                    }
                    if(empty($CFG->dbnotimezone)){
                        if(!empty($CFG->dbtimezone))
                            $db->configTimeZone($CFG->dbtimezone);
                        elseif(!empty($CFG->timezone))
                            $db->configTimeZone($CFG->timezone);
                        else
                            $db->configTimeZone();
                    }
                }
                return $db;
            }catch(\ELIX\DB\dbException $ex){
                error_log($ex->getMessage());
            }
            
        }
        return self::$link;
    }
    public static function init(){
        //this public function auto-connect the DB, only to maintain compatibility
        //with only called to DB::init()  to start the global connection
        self::connect();
        return mysqli_init(); 
    }
    public static function close(){
        if(self::$link) self::$link->close();
        self::$link = null;
    }
    public static function connection(){
        return self::connect(); 
    }
    
    
    public static function build($server, $username, $password, $dbname){
        \ELIX\DB\__deprecated(__FUNCTION__,'use DB::open');
        
        $db = new \ELIX\DB\DBO($server, $username, $password, $dbname);
        return $db;
    }
    
    
    /**
     * DB::insertRows()
     * 
     * @param mixed $table
     * @param mixed $columns 
     *          @STRING = 'col1,col2' 
     *          @ARRAY('col1','col2')
     *          do not enclose in  brackets
     * @param mixed $rows
     *          @STRING = 'col1,col2' NO brackets 
     *          @ARRAY  'col1','col2' implode and used as single row NO brackets
     *          @ARRAY of arrays
     *              ((col1,col2),(col1,col2)))NO brackets 
     * @return
     */
    public static function insertRows($table,$columns,$rows, $ignore=false){
        //DEPRECATING THIS public function
        \ELIX\DB\__deprecated(__FUNCTION__ );
        
        
        if(self::$link === null) self::init();
        if(is_array($table)) $table= implode(',',array_filter($table));
        if(is_array($columns))   $columns= implode(',',array_filter($columns));
        $columns =trim(trim($columns,'('),')');
        //TODO: incomplete
        
        if(is_array($rows)){
            $multiple = false;
            foreach($rows as $k => $row){
                if(is_array($row)){
                    $multiple = true;
                    $row = implode(',',$row);
                    //$row = trim($row,')(')
                    $rows[$k] = "($row)";
                }
            }
            $rows= implode(',',$rows);
            if($multiple ) trigger_error('incomplete insertRows');
        }
        
        if(!$rows) return 0;
        
        $query = $ignore?'INSERT IGNORE INTO':'INSERT INTO';
        $query .= " $table ";
        if($columns)  $query.=" ($columns)";
        if($rows)  $query.=" VALUES ($rows)";
        return self::execute($query);
    }
    /**
     * DB::insertBluk()
     * 
     * @param mixed $table
     * @param mixed $rows  array of arrays or array of lines 
     *    
     * @return void
     */
    public static function insertBulk($table,$fields,$rows,$replace='REPLACE' ,$fieldSeparator=',', $enclosedBy = '"',$lineSeparator="\n"){
        //DEPRECATING THIS public function
        \ELIX\DB\__deprecated(__FUNCTION__ );
        
        
        if(empty($table)) return;
        
        $lines = array();
        foreach($rows as $i=>$row) {
            if(is_array($row)){
                foreach($row as $i=>$r) {
                    $row[$i] = str_replace('\\','\\\\',$row[$i]);
                    if(strpos($r,$fieldSeparator)!==false){
                        $row[$i] = str_replace($fieldSeparator,"\\{$fieldSeparator}",$row[$i]);
                    }
                    if(strpos($r,$enclosedBy)!==false){
                        $row[$i] = str_replace($enclosedBy,"\\$enclosedBy",$row[$i]);
                    }
                    if(strpos($r,$fieldSeparator)!==false || strpos($r,$enclosedBy)!==false){
                        $row[$i] = $enclosedBy . $row[$i].$enclosedBy;
                    }
                }
                $lines[] = implode($fieldSeparator,$row);
            }else
                $lines[] = $row;
        }
        $buf    = implode('',$lines);
        if(empty($lineSeparator) || $lineSeparator=='\n')$lineSeparator = "\n";
        foreach(array($lineSeparator,"\n","^^^","%%%","/*^*/","/*/*/","^^^\n","%%%%\n") as $i){
            if(strpos($buf,$i)===false){
                $lineSeparator =$i;
                break;
            }
        }
        
        $buf    = implode($lineSeparator,$lines).$lineSeparator;
        
        
        
        $in_file = tempnam("/dev/shm/",'db'); 
        if (!@file_put_contents($in_file, $buf)) {
            throw new \ELIX\DB\dbException('Cant write to buffer file: "'.$in_file.'"',11);
            return false;
        }
        
        $fieldSeparator = str_replace("\t",'\t',$fieldSeparator);
        $replace = strtoupper($replace);
        if(is_array($fields))$fields= implode(',',array_filter($fields));
        
        
        $query = "LOAD DATA CONCURRENT LOCAL INFILE '$in_file'";
        if($replace && ($replace=='REPLACE'||$replace=='IGNORE')) $query .= " $replace";
        $query .= " INTO TABLE $table";
        if($fieldSeparator){
            //$fieldSeparator = DB::escape($fieldSeparator);
            $query .= " FIELDS TERMINATED BY '$fieldSeparator'";
        }
        if($enclosedBy    ){
            //$enclosedBy = DB::escape($enclosedBy);
            $query .= " OPTIONALLY ENCLOSED BY '$enclosedBy'";
        }
        
        if($lineSeparator){
            $lineSeparator = str_replace("\n",'\n',$lineSeparator);
            //$lineSeparator = DB::escape($lineSeparator);
            $query .= " LINES TERMINATED BY '$lineSeparator'";
        }
        if($fields)$query .= " ($fields)";
        
        $rs =self::execute($query);
        @unlink($in_file);
        return $rs;
    }
    public static function insertFromFile($file, $table,$fields,$replace='REPLACE' ,$fieldSeparator=',', $enclosedBy = '"',$lineSeparator='\n'){
        //DEPRECATING THIS public function
        \ELIX\DB\__deprecated(__FUNCTION__ );
        
        
        if(empty($file)){
            $file = tempnam(sys_get_temp_dir(), 'db');
        }
        if(empty($table)) return;
        $replace = strtoupper($replace);
        if(is_array($fields))$fields= implode(',',array_filter($fields));
        
        
        $query = "LOAD DATA CONCURRENT LOCAL INFILE '$file'";
        if($replace && ($replace=='REPLACE'||$replace=='IGNORE')) $query .= " $replace";
        $query .= " INTO TABLE $table";
        if($fieldSeparator){
            
            //$fieldSeparator = DB::escape($fieldSeparator);
            $fieldSeparator = str_replace("\t",'\t',$fieldSeparator);
            $query .= " FIELDS TERMINATED BY '$fieldSeparator'";
        }
        if($enclosedBy    ){
            //$enclosedBy = DB::escape($enclosedBy);
            $query .= " OPTIONALLY ENCLOSED BY '$enclosedBy'";
        }
        
        if($lineSeparator){
            //$lineSeparator = DB::escape($lineSeparator);
            $lineSeparator = str_replace("\n",'\n',$lineSeparator);
            $query .= " LINES TERMINATED BY '$lineSeparator'";
        }
        if($fields)$query .= " ($fields)";
        
        return self::execute($query);
    }
    public static function importSQL($file){
        //DEPRECATING THIS public function
        \ELIX\DB\__deprecated(__FUNCTION__ );
        
        
        
        // Temporary variable, used to store current query
        $templine = '';
        // Read in entire file
        $lines = file($file);
        // Loop through each line
        foreach ($lines as $line)
        {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '') continue;
            
            // Add this line to the current segment
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';')
            {
                self::execute($templine);
                $templine = '';
            }
        }
        return 1;
    }
    public static function importSQLcli($file,$db=null){
        //DEPRECATING THIS public function
        \ELIX\DB\__deprecated(__FUNCTION__ );
        
        
        
        GLOBAL $CFG;
		if(!isset($CFG)){
			throw new \ELIX\DB\dbException('$CFG Error (required to have $CFG->dbhost,$CFG->dbuser,$CFG->dbpass in GLOBAL scope to connect to database)', 2);
		}
		if(empty($db)){
            if(isset($CFG->dbname))
                $db = $CFG->dbname;
            else
                throw new \ELIX\DB\dbException('Database name required', 2);
		}
        
		if(empty($file)){
            throw new \ELIX\DB\dbException('SQL file required', 2);
		}
        //TODO return status
        $command='mysql -h' .$CFG->dbhost .' -u' .$CFG->dbuser .' -p' .$CFG->dbpass .' ' .$db .' < ' .$file;
        exec($command,$output=array(),$worked);
        switch($worked){
            case 0:
                return 1;
                //'Import file <b>' .$file .'</b> successfully imported to database <b>' .$db .'</b>';
                break;
            case 1:
                /*throw new \ELIX\DB\dbException( 'There was an error during import. Please make sure the import file is saved in the same folder as this script and check your values:<br/><br/><table><tr><td>MySQL Database Name:</td><td><b>' 
                    .$db .'</b></td></tr><tr><td>MySQL User Name:</td><td><b>' 
                    .$CFG->dbuser .'</b></td></tr><tr><td>MySQL Password:</td><td><b>NOTSHOWN</b></td></tr><tr><td>MySQL Host Name:</td><td><b>' 
                    .$CFG->dbhost .'</b></td></tr><tr><td>MySQL Import Filename:</td><td><b>' 
                    .$file .'</b></td></tr></table>',2);*/
                break;
        }
        return 0;
    }
    
}

