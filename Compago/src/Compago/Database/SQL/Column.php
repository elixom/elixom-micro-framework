<?php

namespace Compago\Database\SQL;

class Column extends \Compago\Database\Core\Column{
    public function __construct($options=array()) {
        if(is_array($options)){
            $this->setColumnOptions($options);
            if(!isset($options['attribute_type'])){
                $this->options['attribute_type'] = 'COLUMN';
            }
        }
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
            if($this->attribute_type=='OPTION'){
                return $this->value;
            }
            if($this->attribute_type=='COLUMN'){
                $f = [];
                
                $def = $this->definition();
                if($this->name && $def){
                    $f[] = "`{$this->name}`";
                    $f[] = $def;
                    if($this->primary) $f[] = "PRIMARY KEY";
                    elseif($this->unique) $f[] = "UNIQUE";
                    return trim(implode(' ',$f));
                }
            }
            if($this->attribute_type =='INDEX' || $this->attribute_type=='KEY'){
                if($this->constraint){
                    $constraint = "CONSTRAINT `$this->constraint` ";
                }else{
                    $constraint ='';
                }
                
                if($this->key_part){
                    $iCol = "({$this->key_part})";
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
                    return trim("{$constraint}UNIQUE {$this->attribute_type} `{$this->name}` {$iCol} {$iOption}");
                }
                if($this->name){
                    $predicate ='';
                    if($this->fulltext) $predicate ='FULLTEXT ';
                    if($this->spatial) $predicate ='SPATIAL '; 
                    return trim("{$predicate}{$this->attribute_type} `{$this->name}` {$iCol} {$iOption}");
                }
            }
            if($this->attribute_type=='CHECK'){
                if($this->expr){
                    return trim("CHECK ({$this->expr})");
                }    
            }
            
        break;
        case 'ALTER':
            if($this->attribute_type=='OPTION'){
                return $this->value;
            }
            if($this->mode=='DROP'){
                if($this->attribute_type =='INDEX' || $this->attribute_type =='KEY'){
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
                if($this->attribute_type =='COLUMN'){
                    if($this->name){
                        return "DROP COLUMN `{$this->name}`";
                    }
                }
                if($this->name){
                    return "DROP {$this->name}";
                }
                return 'DROP';
            }
                
            if($this->attribute_type =='COLUMN'){
                $def = $this->definition();
                $mode = $this->mode;
                $foa ='';
                if($this->after){
                    $foa = "AFTER `$this->after`";
                }elseif($this->first){
                    $foa = 'FIRST';
                }
                if (!$mode){
                    $mode = 'ADD';
                }
                if ($this->new_name && ($this->new_name != $this->name)){
                    $mode = 'CHANGE';
                }
                if(!$def){
                    $mode = 'ALTER';
                }
                
                if($mode=='CHANGE'){
                    if($this->new_name){
                        $new_name = $this->new_name;
                    }else{
                        $new_name = $this->name;
                    }
                    return trim("CHANGE COLUMN `{$this->name}` `{$new_name}` {$def} {$foa}");
                }
                
                if($mode=='MODIFY'){
                    return trim("MODIFY COLUMN `{$this->name}` {$def} {$foa}");
                }
                
                if($mode=='ALTER'){
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
                if($mode=='ADD'){
                    return trim("ADD COLUMN `{$this->name}` {$def} {$foa}");
                }
            }
            if($this->mode=='ADD'){
                if($this->attribute_type =='INDEX' || $this->attribute_type=='KEY'){
                    if($this->constraint){
                        $constraint = "CONSTRAINT `$this->constraint` ";
                    }else{
                        $constraint ='';
                    }
                    
                    if($this->key_part){
                        $iCol = "({$this->key_part})";
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
                        return trim("ADD {$constraint}UNIQUE {$this->attribute_type} `{$this->name}` {$iCol} {$iOption}");
                    }
                    if($this->name){
                        $predicate ='';
                        if($this->fulltext) $predicate ='FULLTEXT ';
                        if($this->spatial) $predicate ='SPATIAL '; 
                        return trim("ADD {$predicate}{$this->attribute_type} `{$this->name}` {$iCol} {$iOption}");
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
}