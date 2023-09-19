<?php

namespace Compago\Database\Blueprint;

//FORMERLY: index_definition
class IndexClause{
    protected $options = array();
    public function toArray(){
        return $this->options;
    }
    public function __get($name) {
        $name = strtolower($name);
        if($name =='index_name' || $name == 'key_name'){
            $name = 'name';
        }
        if(in_array($name,array('indextype','using'))){
            $name = 'index_type';
        }
        if(isset($this->options[$name])){
            return $this->options[$name];
        }
        return null;
    }
    public function __set($name, $value) {
        $name =strtolower($name);
        
        if(in_array($name,array('index_type','indextype','using'))){
            $name = 'index_type';
            $value =strtoupper($value);
        }
        $this->options[$name] = $value;
    }
    
    public function __construct($data =null){
        if($data){
            $this->options = array_change_key_case($data,CASE_LOWER);
        }
    }
    public function toDefinitionArray(){
        $a = array();
        $a['key_part'] = $this->key_part;
        $a['index_type'] = $this->index_type;
        if($this->primary){
            $a['primary'] =  $this->primary;
        }elseif($this->name == 'PRIMARY'){
            $a['primary'] =  true;
        }
        if($this->unique){
            $a['unique'] =  $this->unique;
        }
        if($this->comment){
            $a['comment'] = $this->comment;
        }
        if($this->key_block_size){
            $a['key_block_size'] = $this->key_block_size;
        }
        if($this->references){
            $a['references'] = $this->references;
        }
        if(isset($this->options['is_visible'])){
            $a['is_visible'] = $this->is_visible;
        }
        return $a;
    }
    public function definition() {
        $f =array();
        if(!$i->key_part){
            if(empty($i->key_part_array))
                return '';
            $i->key_part = implode(',',$i->key_part_array);
        }
        
        $f[] = "($i->key_part)";
        
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
        if(isset($this->options['is_visible'])){
            $f[] = $this->is_visible?'VISIBLE':'NOT VISIBLE';
        }
        return implode(' ',$f);
    }
}