<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;

if(!defined('EXPR_AND')) define('EXPR_AND','AND');
if(!defined('EXPR_OR')) define('EXPR_OR','OR');
if(!defined('EXPR_PLAIN')) define('EXPR_PLAIN','');

class expr{
    private $type = '';
    public $not = false;
    public $left = '';
    public $right = '';
    public $right2 = ''; //for between and if  
    public $operator = '=';
    
    public function type($type=''){
        if(func_num_args()){
            $type = strtoupper($type);
            if(in_array($type,array('','AND','OR','SET')))
                $this->type = $type;
        }
        return $this->type;
    }
    public function raw($name) {
        $a = array();
        if($this->type=='SET'){
            $a[] = "{$this->left}";
            $a[] = $this->operator;
            $a[] = "{$this->right}";
        }else{
            if($this->type) $a[] = $this->type;
            if($this->not) $a[] = "NOT";
            $this->operator = strtoupper(trim($this->operator));
            if(in_array($this->operator ,array('=','<>','>','<','>=','<=','LIKE','IN'))){
                $a[] = "({$this->left}";
                $a[] = $this->operator;
                $a[] = "{$this->right})";
            }elseif(in_array($this->operator ,array('IS','IS NOT'))){
                $a[] = "({$this->left}";
                $a[] = $this->operator;
                if(is_null($this->right) || $this->right =='NULL')
                    $a[] = "NULL)";
                else
                    $a[] = "{$this->right})";
            }elseif(in_array($this->operator ,array('IS NULL','IS NOT NULL'))){
                $a[] = "({$this->left} $this->operator)";
            }elseif(in_array($this->operator ,array('IFNULL','FIND_IN_SET'))){
                $a[] = "$this->operator({$this->left},{$this->right})";
            } elseif(in_array($this->operator ,array('ISNULL','MD5','COUNT','MAX','MIN','SUM','AVG'))){
                $a[] = "$this->operator({$this->left})";
            }elseif( $this->operator == 'BETWEEN'){
                $a[] = "{$this->left} BETWEEN {$this->right} AND {$this->right2}";
            }elseif( $this->operator == 'IF'){
                $a[] = "IF({$this->left},{$this->right},{$this->right2})";
            }else{
                if(empty($this->right) && empty($this->operator))
                    $a[] = "{$this->left}";
            }
        }
        return implode(' ', $a);
    }
     
    public function __toString() {
        $w = $this->raw();
        if($w)
            return $w;
        else
            return '';
        
    }
}

?>