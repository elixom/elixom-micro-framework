<?php

namespace Compago\Database\Blueprint;

class IndexAttribute extends \Compago\Database\Core\Attribute{
    public function toString(){
        return $this->definition();
    }
    public function __construct($data =null){
        if(is_array($data)){
            $this->setIndexOptions($data);
        }
    }
    public function definition(){
        $f =array();
        if($this->key_part){
            $f[] = "($this->key_part)";
        } else {
            if(empty($this->key_part_array))
                return '';
            $this->key_part = implode(',',$this->key_part_array);
            $f[] = "($this->key_part)";
        }
        
        if($this->key_block_size){
            $f[] = "KEY_BLOCK_SIZE {$this->key_block_size}";
        }
        if($this->index_type){
            $iType = strtoupper($this->index_type);
            if(in_array($iType,array('BTREE','HASH'))){
                $f[] = "USING $iType";
            }
        }
        if($this->parser) $f[] = "WITH PARSER {$this->parser}";
        if($this->comment) $f[] = "COMMENT '{$this->comment}'";
        return implode(' ',$f);
    }
    public function __get($name) {
        $name = strtolower($name);
        if($name =='key_name' || $name =='index_name'){
            $name = 'name';
        }
        if(in_array($name,array('indextype','using'))){
            $name = 'index_type';
        }
        if($name =='index_comment'){
            $name = 'comment';
        }
        if(isset($this->options[$name])){
            return $this->options[$name];
        }
        return null;
    }
    public function __set($name, $value) {
        $name =strtolower($name);
        if($name =='key_name' || $name =='index_name'){
            $this->setName($value);
            return;
        }
        if(in_array($name,array('index_type','indextype','using'))){
            $name = 'index_type';
            $value =strtoupper($value);
        }
        if($name =='index_comment'){
            $name = 'comment';
        }
        $this->options[$name] = $value;
    }
    
    public function setName($data){
        $this->options['name'] = $data;
        if(strtoupper($data) =='PRIMARY'){
            $this->options['primary'] = true;
            $this->options['name'] = 'PRIMARY';
        }
    }
    
    /*
            Table: city
   Non_unique: 1
     Key_name: CountryCode
 Seq_in_index: 1
  Column_name: CountryCode
    Collation: A
  Cardinality: 4321
     Sub_part: NULL
       Packed: NULL
         Null: 
   Index_type: BTREE
      Comment: 
Index_comment:
SHOW INDEX returns the following fields:

Table-The name of the table.
Non_unique-0 if the index cannot contain duplicates, 1 if it can.
Key_name-The name of the index. If the index is the primary key, the name is always PRIMARY.
Seq_in_index-The column sequence number in the index, starting with 1.
Column_name-The column name.
Collation- How the column is sorted in the index. This can have values A (ascending) or NULL (not sorted).
Cardinality-An estimate of the number of unique values in the index. To update this number, run ANALYZE TABLE or (for MyISAM tables) myisamchk -a.
    Cardinality is counted based on statistics stored as integers, so the value is not necessarily exact even for small tables. The higher the cardinality, the greater the chance that MySQL uses the index when doing joins.

Sub_part -The index prefix. That is, the number of indexed characters if the column is only partly indexed, NULL if the entire column is indexed.
    Note
    Prefix limits are measured in bytes, whereas the prefix length in CREATE TABLE, ALTER TABLE, and CREATE INDEX statements is interpreted as number of characters for nonbinary string types (CHAR, VARCHAR, TEXT) and number of bytes for binary string types (BINARY, VARBINARY, BLOB). Take this into account when specifying a prefix length for a nonbinary string column that uses a multibyte character set.
    For additional information about index prefixes, see Section 8.3.4, “Column Indexes”, and Section 13.1.14, “CREATE INDEX Syntax”.
Packed -Indicates how the key is packed. NULL if it is not.
Null - Contains YES if the column may contain NULL values and '' if not.
Index_type -The index method used (BTREE, FULLTEXT, HASH, RTREE).
Comment -Information about the index not described in its own column, such as disabled if the index is disabled.
Index_comment - Any comment provided for the index with a COMMENT attribute when the index was created.
*/

}
