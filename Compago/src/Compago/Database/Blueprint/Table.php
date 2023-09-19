<?php

namespace Compago\Database\Blueprint;


class Table{
    protected $options = array();
    protected $columns = array();
    protected $indexes =array();
    protected $errors =array();
    public function __get($name) {
        $name = strtolower($name);
        if($name =='table_name'){
            $name = 'name';
        }
        if($name =='table_rows'){
            $name = 'rows';
        }
        if($name =='new_table_name' || $name =='newname'){
            $name = 'new_name';
        }
        if($name =='collation' || $name =='table_collation'){
            $name = 'collate';
        }
        if(isset($this->options[$name])){
            return $this->options[$name];
        }
        return null;
    }
    public function __set($name, $value) {
        $name =strtolower($name);
        if($name =='table_name'){
            $name = 'name';
        }
        if($name =='table_rows'){
            $name = 'rows';
            $value = (int)$value;
        }
        if($name =='new_table_name' || $name =='newname'){
            $name = 'new_name';
        }
        if($name =='collation' || $name =='table_collation'){
            $name = 'collate';
        }
        
        $this->options[$name] = $value;
    }
    public function __construct($data =null){
        if($data){
            if(is_string($data)){
                $a = @json_decode($data,true);
                if(is_array($a)){
                    $data = $a;
                }else if ($a ===null){
                    $this->errors[] = 'JSON ERROR: ' . json_last_error();
                    $this->errors[] = 'JSON STRING: ' . $data;
                }
            }
            if(is_array($data)){
                if(isset($data['name'])){
                    $this->setName($data['name']);
                }
                if(isset($data['options'])){
                    $this->setTableOptions($data['options']);
                }
                if(isset($data['columns'])){
                    $this->setColumns($data['columns']);
                }
                if(isset($data['keys'])){
                    $this->setIndexes($data['keys']);
                }
                if(isset($data['indexes'])){
                    $this->setIndexes($data['indexes']);
                }
            }
        }
    }
    public function getSql(){
        $a = array();
        $a[] = 'CREATE TABLE';
        if($this->if_not_exists){
            $a[] = 'IF NOT EXISTS';
        }
        if ($this->new_name){
            $a[] = "`{$this->new_name}`(";
        } else {
            $a[] = "`{$this->name}`(";
        }
        $i = 0;
        foreach($this->columns as $c){
            $def = $c->definition();
            if ($def){
                $n =  ($c->new_name)?$c->new_name:$c->name;
                $a[] = ($i>0?',':'') ."`$n` $def";
                $i++;
            }
        }
        
        foreach($this->getIndexDefinitions() as $c){
            $a[] = ($i>0?',':'') . $c;
            $i++;
        }
        $a[] = ')';
        
        if(!empty($this->options['auto_increment'])){
            $temp = (int)$this->options['auto_increment'];
            $a[] = "AUTO_INCREMENT={$temp}";
        }
        if(!empty($this->options['charset'])){
            $a[] = "DEFAULT CHARACTER SET={$this->options['charset']}";
        }
        if(!empty($this->options['collate'])){
            $a[] = "DEFAULT COLLATE={$this->options['collate']}";
        }
        if(!empty($this->options['checksum'])){
            $temp = (int)$this->options['checksum'];
            $a[] = "CHECKSUM={$temp}";
        }
        if(!empty($this->options['comment'])){
            $a[] = "COMMENT='{$this->options['comment']}'";
        }
        if(!empty($this->options['engine'])){
            $a[] = "ENGINE={$this->options['engine']}";
        }
        if(!empty($this->options['row_format'])){
            $a[] = "ROW_FORMAT={$this->options['row_format']}";
        }
        
        return implode(' ', $a);
    }
    public function getIndexDefinitionArray(){
        $a = array();
        foreach($this->indexes as $c){
            if($c->primary){
                $n = 'PRIMARY';
            }else{
                $n = $c->name;
            }
            if(!isset($a[$n])){
                $a[$n] = array('name'=>$n,'unique'=>1);
                if($n=='PRIMARY'){
                    $a[$n]['primary'] = true;
                }
                $a[$n]['key_part_array'] = array();
            }
            if($c->column_name){
                $s = (int)$c->sub_part;
                if($s){
                    $col = "`{$c->column_name}`({$s})";
                }else{
                    $col = "`{$c->column_name}`";
                }
                if($c->seq_in_index){
                   $s = $c->seq_in_index;
                   $a[$n]['key_part_array'][$s] = $col; 
                }else{
                   $a[$n]['key_part_array'][] = $col; 
                }
            }
            if($c->comment){
                $a[$n]['comment'] = $c->comment;
            }
            if($c->index_type){
                $a[$n]['index_type'] = $c->index_type;
            }
            if($c->non_unique){
                $a[$n]['unique'] = 0;
            }
        }
        $b =array();
        foreach($a as $i){
            $i['key_part_array'] = array_values($i['key_part_array']);
            $i['key_part'] = implode(',', $i['key_part_array']);
            $b[] = new IndexClause($i);
        }
        return $b;
    }
    public function getIndexDefinitions(){
        $b =array();
        $a = $this->getIndexDefinitionArray();
        foreach($a as $i){
            if($i->name == 'PRIMARY'){
                $n = "PRIMARY KEY";
            }else if($i->unique){
                $n = "UNIQUE INDEX `{$i->name}`";
            }else{
                $n = "INDEX `{$i->name}`";
            }
            $n .= " ($i->key_part)";
            if(isset($i->index_type) && ($i->index_type !== 'BTREE')){
                $n .= " USING {$i->index_type}";
            }
            $b[] = $n;
        }
        return $b;
    }
    public function getJson($return_useless_data=false){
        $a = array();
        if(count($this->options)){
            $a['options'] = $this->options;
            if(!$return_useless_data){
                unset($a['options']['dbname']);
                //unset($a['options']['table_type']);
                unset($a['options']['rows']);
                unset($a['options']['avg_row_length']);
                unset($a['options']['data_length']);
                unset($a['options']['max_data_length']);
                unset($a['options']['index_length']);
                unset($a['options']['data_free']);
                unset($a['options']['auto_increment']);
                //unset($a['options']['create_time']);
                //unset($a['options']['update_time']);
                unset($a['options']['check_time']);
            }
        }
        if(count($this->columns)){
            $a['columns'] = array();
            foreach($this->columns as $c){
                $ca = $c->toArray();
                if(!$return_useless_data){
                    unset($ca['primary']);
                    unset($ca['privileges']);
                    unset($ca['key']);
                }
                $a['columns'][] = $ca;
            }
        }
        if(count($this->indexes)){
            $a['indexes'] = array();
            foreach($this->indexes as $c){
                $ca = $c->toArray();
                if(!$return_useless_data){
                    unset($ca['collation']);
                    unset($ca['cardinality']);
                    unset($ca['packed']);
                    unset($ca['null']);
                }
                if($c->primary){
                    unset($ca['name']);
                }
                $a['indexes'][] = $ca;
            }
        }
        return json_encode($a,JSON_PRETTY_PRINT);
    }
    
    public function setOptions($options){
        $this->setTableOptions($options);
    }
    public function setTableOptions($options){
        if(!is_array($options)){
            return;
        }
        $options = array_change_key_case($options,CASE_LOWER);
        unset($options['table_catalog']);
        foreach($options as $key=>$value){
            if(in_array($key,array('type','table_type'))){
                $this->options['table_type'] = $value;
            }elseif(in_array($key,array('collate','table_collation','collation','collation_name'))){
                $this->options['collate'] = $value;
            }elseif(in_array($key,array('charset','char_set','character set','characterset','character_set'))){
                $this->options['charset'] = $value;
            }elseif(in_array($key,array('name','table','table_name','tablename'))){
                $this->options['name'] = $value;
            }elseif(in_array($key,array('dbname','table_schema','schema','db','database','database_name'))){
                $this->options['dbname'] = $value;
            }elseif(in_array($key,array('auto_increment','autoincrement'))){
                $this->options['auto_increment'] = (int)$value;
            }elseif(in_array($key,array('table_rows','rows'))){
                $this->options['rows'] = (int)$value;
            }elseif(in_array($key,array('row_format','format'))){
                $this->options['row_format'] = strtoupper($value);
            }elseif(in_array($key,array('comment','table_comment'))){
                $this->options['comment'] = $value;
            }else{
                //version,comment
                $this->options[$key] = $value;
            }
        }
    }
    public function setName($data){
        $this->options['name'] = $data;
    }
    public function rename($data){
        $this->options['new_name'] = $data;
    }public function hasColumns(){
        return count($this->columns);
    }
    private function setColumns($data){
        $this->columns =array();
        foreach($data as $a){
            $this->addColumn($a);
        }
    }
    public function addColumn($colName,$colOptions=array()){
        if(func_num_args()==2){
            $colOptions['name'] = $colName;
        }else if(is_array($colName)){
            $colOptions = array_merge($colName,$colOptions);
        }else{
            $colOptions['name'] = $colName;
        }
        $this->columns[] = new ColumnAttribute($colOptions);
    }
    public function hasColumn($name=null){
        if($name){
            foreach($this->columns as $c){
                if($c->name == $name){
                    return true;
                }
            }
            return false;
        }else{
            return count($this->columns);
        }
    }
    public function getColumn($name=null){
        if($name){
            foreach($this->columns as $c){
                if($c->name == $name){
                    return $c;
                }
            }
        }
        return new ColumnAttribute();
    }
    public function getColumns(){
        return $this->columns;
    }
    private function setIndexes($data){
        $this->indexes =array();
        foreach($data as $a){
            $this->addIndex($a);
        }
    }
    public function addIndex($indexName,$indexOptions=array()){
        if(func_num_args()==2){
            $indexOptions['name'] = $indexName;
        }else if(is_array($indexName)){
            $indexOptions = $indexName;
        }else{
            $indexOptions['name'] = $indexName;
        }
        $this->indexes[] = new IndexAttribute($indexOptions);
    }
    public function hasIndex($name=null){
        if($name){
            foreach($this->indexes as $c){
                if($c->name == $name){
                    return true;
                }
            }
            return false;
        }else{
            return count($this->indexes);
        }
    }
    public function getIndex($name=null){
        if($name){
            foreach($this->getIndexDefinitionArray() as $c){
                if($c->name == $name){
                    return $c;
                }
            }
        }
        return new IndexClause();
    }
    
    
}
