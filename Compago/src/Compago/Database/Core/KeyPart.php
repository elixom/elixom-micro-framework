<?php
/**
 * @author Edwards
 * @copyright 2020
 */
namespace Compago\Database\Core;

class KeyPart{
    protected $column_name;
    protected $expr;
    protected $collation;
    protected $sub_part;
    public function __construct($data =null,$collation =null,$sub_part =null){
        if(is_array($data)){
            if (isset($data['sub_part'])){
                $this->__set('sub_part',$data['sub_part']);
            }
            if (isset($data['collation'])){
                $this->__set('collation',$data['collation']);
            }
            if (isset($data['expr'])){
                $this->__set('expr',$data['expr']);
            }
            if (isset($data['column_name'])){
                $this->__set('column_name',$data['column_name']);
            }
        } else {
            if ($sub_part !== null){
                $this->__set('sub_part',$sub_part);
            }
            if ($collation !== null){
                $this->__set('collation',$collation);
            }
            if ($data !== null){
                $c1 = substr($data,0,1);
                if ($c1 == '`'){
                    $this->__set('column_name',$data);
                } elseif ($c1 == '('){
                    $this->__set('expr',$data);
                } else {
                    $this->__set('column_name',$data);
                }
            }
        }
    }
    
    public function __set($name, $value) {
        $name =strtolower($name);
        if($name =='expression'){
            $name = 'expr';
        }
        if($name =='subpart'){
            $name = 'sub_part';
        }
        if (!empty($value)){
            if($name =='sub_part'){
                $value = (int)$value;
            }
            if($name =='column_name'){
                $value = trim($value,'`');
            }
            if($name =='expr'){
                $value = ltrim($value,'(');
                $value = rtrim($value,')');
            }
            if($name =='collation'){
                $value =strtoupper($value);
                $value =substr(trim($value),0,1);
            }
            if($name == 'expr'){
                $this->column_name = null;
            }
            if($name == 'column_name'){
                $this->expr = null;
            }
        }
        if(in_array($name,array('expr','column_name','sub_part','collation'))){
            $this->$name = $value;
        }
    }
    
    public function __get($name) {
        $name =strtolower($name);
        if($name =='expression'){
            $name = 'expr';
        }
        if($name =='subpart'){
            $name = 'sub_part';
        }
        if(in_array($name,array('columnname','field','field_name','fieldname'))){
            $name = 'column_name';
        }
        if(in_array($name,array('expr','column_name','sub_part','collation'))){
            return $this->$name;
        }
    }
    public function toString(){
        $f = [];
        if ($this->expr){
            $f[] = "($this->expr)";
        }elseif ($this->column_name){
            $f[] = "`$this->column_name`";
            if ($this->sub_part){
                $f[] = "($this->sub_part)";
            }
        } else {
            return '';
        }
        
        if ($this->collation == 'D'){
            $f[] = " DESC";
        }
        return implode('',$f);
    }
    public function toArray(){
        return [
            'column_name' => $this->column_name,
            'expr' => $this->expr,
            'collation' => $this->collation,
            'sub_part' => $this->sub_part
        ];
    }
    public function __toString() {
        return $this->toString();
    }
}