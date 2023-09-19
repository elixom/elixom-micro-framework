<?php
/**
 * @author Shane Edwards
 * @copyright 2018
 */
namespace Compago\Html;

function is_node_of($node,$type=''){
    if (!is_object($node)){
        return false;
    }
    if ($node instanceof node ){
        if (is_array($type)){
            return in_array($node->tagName(),$type);
        }
        $type = strtolower($type);
        return $node->tagName() == $type;
    }
    return false;
}
class node{
    protected $tag = 'div';
    protected $attr  = array();
    protected $nodes  = array();
    protected $parent = null;
    
    public function __construct() {
        if(func_num_args() && func_get_arg(0)){
            $param = func_get_arg(0);
            if ($param instanceof node ){
                $this->parent = $param;
            } elseif(is_scalar($param)){
                $this->tag = strtolower($param);
            } elseif(is_array($param)){
                $this->attr($param);
            }
        }
    }
    public function __toString() {
        $r = array();
        if($this->isVoidElement())
        {
            return $this->getOpenTag();
        }
        $r[] = $this->getOpenTag();
        $temp = $this->innerHTML();
        
        if ($this->tag == 'textarea'){
            $temp = htmlspecialchars($temp, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, null,false);
        }
        $r[] = $temp;
        $r[] = $this->getCloseTag();
        $sep = ($temp=='')?'':HtmlUtils::getSeparator($this->tag);   
        //TODO consider: for option tags if the content is empty do not add close tag
        return implode($sep,$r);
    }
    public function tagName(){
        return strtolower($this->tag);
    }
    protected function isVoidElement()
    {
        return in_array($this->tag,array("area", "base", /*"basefont",*/ "br", "col",/*"command",*/"embed",/* "frame",*/ "hr", "img", "input","keygen", "link", "meta", "param", "source", "track", "wbr"));
    }
    public function reset()
    {
        $this->nodes= array();
        $this->attr= array();
        return $this;
    }
    protected function _empty()
    {
        $this->nodes= array();
        return $this;
    }
    public function isEmpty()
    {
        return $this->innerHTML()=='';
    }
    public function __call($name, $arguments) {
        $lname = strtolower($name);
        $n = count($arguments);
        if($lname=='empty') {
            return $this->_empty();
        }
        if($n==0)
            return static::attr($name);
        else
            return static::attr($name,$arguments[0]);
    }
    public function data($key='',$value=null)
    {
        $n =func_num_args();
        if($n == 0){
            $d =array();
            foreach($this->attr as $k => $v){
                if(strtolower(substr($k,0,5))=='data-'){
                    $d[substr($k,5)] =& $this->attr[$k];
                }
            }
            return $d;
        }elseif($n==2){
            $key = "data-{$key}";
            if((null ===$value) || (is_bool($value) && $value == false))
                unset($this->attr[$key]);
            else{
                $this->attr[$key] =$value;
            }
            return $this;
        }elseif($n==1){
            if(is_array($key)){
                foreach($key as $k=>$v){
                    $this->data($k,$v);
                }
                return $this;
            }
            $key = "data-{$key}";
            if(isset($this->attr[$key]))
                return $this->attr[$key];
            else
                return null;
        }
    }
    public function aria($key='',$value=null)
    {
        $n =func_num_args();
        if($n == 0){
            $d =array();
            foreach($this->attr as $k => $v){
                if(strtolower(substr($k,0,5))=='aria-'){
                    $d[substr($k,5)] =& $this->attr[$k];
                }
            }
            return $d;
        }elseif($n==2){
            $key = "aria-{$key}";
            if((null ===$value) || (is_bool($value) && $value == false))
                unset($this->attr[$key]);
            else{
                $this->attr[$key] =$value;
            }
                
            return $this;
        }elseif($n==1){
            if(is_array($key)){
                foreach($key as $k=>$v){
                    $this->aria($k,$v);
                }
                return $this;
            }
            $key = "aria-{$key}";
            if(isset($this->attr[$key]))
                return $this->attr[$key];
            else
                return null;
        }
    }
    
    public function attr($key='',$value=null)
    {
        $n =func_num_args();
        if($n == 0){
            return $this->attr;
        }elseif($n==2){
            if((null ===$value) || (is_bool($value) && $value == false))
                unset($this->attr[$key]);
            else{
                $this->attr[$key] =$value;
            }   
            return $this;
        }elseif($n==1){
            if(is_array($key)){
                foreach($key as $k=>$v){
                    $this->attr($k,$v);
                }
                return $this;
            }
            if(isset($this->attr[$key]))
                return $this->attr[$key];
            else
                return null;
        }
    }
    public function getAttributes()
    {
        $a = $this->attr;
        if(func_num_args()){
            $r = func_get_arg(0);
            if(is_array($r)){
                $a = array_merge($this->attr,$r);
            }
        }
        $r = array();
        foreach($a as $k => $v)
        {
            if(is_bool($v))
            {
                if($v) $r[] =$k;
            }else
            {
                $v = htmlspecialchars($v,ENT_QUOTES,null,false);
                $r[] ="$k='$v'";
            }     
        }
        return trim(implode(' ',$r));
    }
    public function getOpenTag()
    {
        $a = $this->getAttributes();
        if(empty($a)) return "<{$this->tag}>";
        
        $r[] = "<{$this->tag}";
        $r[] = $a .'>';
        return implode(' ',$r);
    }
    public function getCloseTag()
    {
        if($this->isVoidElement())return '';
        return "</{$this->tag}>";
    }
    public function node($nodeIndex=0)
    {
        if(func_num_args()==0){
            return $this->nodes;
        }elseif(isset($this->nodes[$nodeIndex])){
            return $this->nodes[$nodeIndex];
        }else{
            return null;
        } 
    }
    public function nodes()
    {
        if(func_num_args()==0){
            return $this->nodes;
        }
        else
        {
            $nodeIndex=func_get_arg(0);
            if(isset($this->nodes[$nodeIndex])){
                return $this->nodes[$nodeIndex];
            }else{
                return null;
            }
        } 
    }
    public function remove($node)
    {
        if(is_numeric($node))
        {
            if($node==-1) $node = count($this->nodes)-1;
            if(isset($this->nodes[$node]))
            {
                unset($this->nodes[$node]);
            }
        }else{
            foreach($this->nodes as $i => $item){
                if($node === $item){
                    unset($this->nodes[$i]);
                }
            }
        }
        return $this;
    }
    public function innerHTML($value = null)
    {
        if(func_num_args()==0){
            $r = array();
            foreach($this->nodes as $i)
                $r[] = (string)$i;
                
            $joiner = HtmlUtils::getSeparator($this->tag);
            return trim(implode($joiner,$r),$joiner);
        }
        if(is_array($value))
            $this->nodes = $value;
        else 
            $this->nodes = array($value);
        return $this;
    }
    
    public function create($tag)
    {
        $el = HtmlUtils::create($tag);
        $n = func_num_args();
        if($n > 1){
            $name =func_get_arg(1);
            if(is_array($name)){
                $el->attr($name);
            }elseif($el instanceof input){
                $el->name($name);
            }else{
                $el->id($name);
            }
        }
        if($n > 2){
            $value =func_get_arg(2);
            if($el instanceof input){
                $el->value($value);
            }else{
                $el->innerHtml($value);
            }
        }
        /*if(get_class($this) != __CLASS__){
            $el->parent($this);
        }*/
        $this->nodes[] = $el;
        return $el;
    }
    public function prepend($value=''){
        if($n = func_num_args())
        {
            if($n>1)
            {
                foreach(func_get_args() as $v) $this->prepend($v);
            }elseif(is_array($value))
            {
                $value = array_reverse($value);
                foreach($value as $v){
                    $this->prepend($v);
                }
            }else{
                if(strlen($value) && (trim($value)=='')){
                    $value = str_replace(' ','&nbsp;',$value);
                }
                array_unshift($this->nodes,$value);
            }
        }
        return $this;
    }
    public function append($value='')
    {
        if($n = func_num_args())
        {
            if($n>1)
            {
                foreach(func_get_args() as $v) $this->append($v);
            }elseif(is_array($value))
            {
                foreach($value as $v) $this->append($v);
            }else{
                $this->nodes[] = $value;
            }
        }
        return $this;
    }
}