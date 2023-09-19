<?php
/**
 * @author Edwards
 * @copyright 2020
 */
namespace Compago\Database\Core;

//todo Incompelte
class PartitionBag{
    protected $data =array();
    
    public function clear() {
        $this->data= array();
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
    public function toString() {
        $f=array();
        foreach($this->data as $name => $value){
            $f[] = "$value";
        }
        return implode(', ', $f);
    }
    public function __toString() {
        return $this->toString();
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