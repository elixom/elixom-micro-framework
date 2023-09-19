<?php
/**
 * @author Shane Edwards
 * @copyright 2018
 */
namespace Compago\Html;

class Input extends element{
    protected $tag = 'input';
    protected $attr  = array('type'=>'text');
    public function __construct() {
        if(func_num_args() && func_get_arg(0)){
            $param = func_get_arg(0);
            if ($param instanceof node ){
                $this->parent = $param;
            } elseif(is_scalar($param)){
                $this->attr('type',$param);
            } elseif(is_array($param)){
                $this->attr($param);
            }
        }
    }
    protected function isVoidElement() { return true; }
    public function value($value=null)
    {
        if(func_num_args()==0)
            return $this->attr(__FUNCTION__);
        else{
            return $this->attr(__FUNCTION__,$value);
        }
    }
    public function __call($name, $arguments) {
        $lname = strtolower($name);
        $n = count($arguments);
        if($lname=='settype') {
            if ($n){
               $this->attr('type',$arguments[0]); 
            }
            return $this;
        }
        if($lname=='type') {
            if ($n){
               $this->attr('type',$arguments[0]); 
            } else {
                $attr_type = strtolower($this->attr('type'));
                if (!$attr_type){
                    $attr_type = 'text';
                    $this->attr['type'] = $attr_type;
                }
                return $attr_type;
            }
            return $this;
        }
        if($lname=='checked') {
            $attr_type = strtolower($this->attr('type'));
            if ($attr_type == 'checkbox-group'){
                return $this->_call_checked_checkbox($arguments);
            }
            if ($attr_type == 'radio-group'){
                return $this->_call_checked_radio($arguments);
            }
        }
        return parent::__call($name, $arguments);
    }
    protected function _call_checked_radio($arguments)
    {
        $setThem = (count($arguments)>0); 
        foreach($this->nodes as $item)
        {
            if($setThem){
                if(in_array($item->value(), $arguments))
                {
                    $item->checked(true);
                }else{
                    $item->checked(false);
                }   
            }else
            {
                if($item->checked()){
                    return $item;
                }    
            }
        }
        return null;
    }
    protected function _call_checked_checkbox($arguments)
    {
        $setThem = (count($arguments)>0);
        if($setThem){
            //if(is_array($arguments[0])) $value = explode(',',$value);
        }else
        {
            $r = array();
        }
        foreach($this->nodes as $item)
        {
            if($setThem){
                if(in_array($item->value(), $arguments))
                {
                    $item->checked(true);
                }
            }else {
                if($item->checked()){
                    $r[] = $item;
                }
            }
        }
        if($setThem){
            return null;
        }else{
            return $r;
        }
            
    }
    public function addOption($label='',$value=null, $checked=false) 
    {
        $attr_type = strtolower($this->attr('type'));
        
        if (!in_array($attr_type,array('radio','radio-group','checkbox','checkbox-group'))){
            return null;
        }
        if (in_array($attr_type,array('radio','checkbox'))){
            $this->attr['type'] = $attr_type .'-group';
        } else {
            $attr_type = substr($attr_type,0,-6);
        }
        
        $attr_name = $this->attr('name');
        $el = new input($attr_type);
        if ($attr_type == 'radio'){
            $el->name($attr_name);
        } else {
            $el->name($attr_name.'[]');
        }
        
        if(func_num_args()==1 || (null ===$value)) $value = $label;
        $el->value($value);
        $el->label($label);
        if(is_scalar($label)){
            $el->label()->attr('title',$label);
        }
        
        if($checked)
        {
            if ($attr_type == 'radio'){
                foreach($this->nodes as $item){
                    $item->checked(false);
                }
            }
            $el->checked(true);
        }
        
        $this->nodes[] =$el;
        return $el;
    }
    public function label($value=null)
    {
        $attr_type = strtolower($this->attr('type'));
        if (in_array($attr_type,array('radio','checkbox'))){
            
            if (isset($this->nodes[0])){
                $el = $this->nodes[0];
            } else{
                $el = HtmlUtils::create('label');
                $this->nodes[0] = $el;
            }
            if(func_num_args()==0){
                return $el;
            }elseif(is_node_of($value,'label')){
                $el = $value;
                $this->nodes[0] = $el;
            }else{
                $el->append($value);
            }
            return $el;
        }
        if(func_num_args()==0){
            parent::attr('label');
        }else{
            parent::attr('label',$value);
        }
    }
    public function __toString() {
        $attr_type = strtolower($this->attr('type'));
        if (in_array($attr_type,array('radio-group','checkbox-group'))){
            $r = array();
            $f = array();
            
            $r[] = "<span";
            $f['name'] = false;
            if(!isset($this->attr['class'])){
                $f['class'] = $attr_type;
            }else{
                $this->addClass($attr_type);
            }
            $r[] = $this->getAttributes($f) .' >';
            
            foreach($this->nodes as $node)
            {
                $label = $node->node(0);
                if((null !==$label)){
                    $r[] = $label->getOpenTag();
                    $r[] =(string)$node;
                    $r[] = $label->innerHTML();
                    $r[] = $label->getCloseTag();
                }else{
                    $r[] =(string)$node;
                }
            }
            $o[] = '</span>';
            return  implode('',$r);
        }
        return parent::__toString();
    }
    public function attr($key='',$value=null)
    {#propogate to all children
        $n =func_num_args();
        $set_key = strtolower($key);
        if (isset($this->attr['type'])){
            $attr_type = strtolower($this->attr['type']);
        } else {
            $attr_type = '';
        }
        
        
        if (in_array($attr_type,array('radio-group','checkbox-group'))){
            if($set_key=='name'){
                if($value){
                    $set_value = ($attr_type=='radio-group')? $value: $value.'[]';
                }else{
                    $set_value = null;
                }
                if($n == 1 && (isset($this->attr[$set_key]))){
                    return $this->attr[$set_key];
                }
                foreach($this->nodes as $node)
                {
                    if($n == 1){
                        $value = $node->name();
                        if($value){
                            break;
                        }
                    }else{
                        $node->name($set_value);
                    }
                }
                if($n == 1){
                    return $value;
                }
                $this->attr[$set_key] = $value;
                return $this;
            }
        }
        if($n == 0){
            return parent::attr();
        }elseif($n==2){
            return parent::attr($key,$value);
        }elseif($n==1){
            return parent::attr($key);
        }
    }
}