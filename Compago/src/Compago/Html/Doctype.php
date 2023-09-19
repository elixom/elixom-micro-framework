<?php
/**
 * @author Shane Edwards
 * @copyright 2018
 */
namespace Compago\Html;


class Doctype{
    protected $tag = 'doctype';
    protected $type='html';
    protected $domain ='';
    protected $lang='EN';
    protected $flavor ='';
    protected $dtd='';
    protected $dtdversion=null;
    protected $dtdurl='';
    public function __construct() {
        if(func_num_args()){
            $param = func_get_arg(0);
            $this->setHTML($param);
        }
    }
    public function setHtml5(){
        $this->flavor = '';
        $this->type = 'html';
        $this->domain = '';
        $this->lang = '';
        $this->dtd ='';
        $this->dtdversion = 5;
        $this->dtdurl='';
    }
    public function setHTML($type='STRICT',$version='4.01'){
        $type = trim(strtoupper($type));
        
        $this->type = 'html';
        $this->flavor = $type;
        $this->domain = 'PUBLIC';
        $this->dtdversion = $version;
        $this->lang = 'EN';
        if($type == 'TRANSITIONAL'||$type == 'LOOSE'){
            $this->dtdurl='http://www.w3.org/TR/html4/loose.dtd';
            $this->dtd ="-//W3C//DTD HTML {$this->dtdversion} Transitional//EN";
            $this->type = 'HTML';
        }elseif($type == 'FRAMESET'){
            $this->dtdurl='http://www.w3.org/TR/html4/frameset.dtd';
            $this->dtd ="-//W3C//DTD HTML {$this->dtdversion} Frameset//EN";
            $this->type = 'HTML';
        }elseif($this->dtdversion == 2){
            $this->dtdurl='';
            $this->dtd ="-//IETF//DTD HTML 2.0//EN";
        }elseif($this->dtdversion == 3.2){
            $this->dtdurl='';
            $this->dtd ="-//W3C//DTD HTML 3.2 Final//EN";
        }elseif($this->dtdversion >= 5 || $type =='HtmlUtils'){
            $this->setHtml5();
            $this->flavor = $type;
        }else{
            $this->type = 'HTML';
            $this->dtdurl='http://www.w3.org/TR/html4/strict.dtd';
            $this->dtd ="-//W3C//DTD HTML {$this->dtdversion}//EN";
        }
        return $this;
    }
    public function setXHTML($type='',$version=null){
        $type = trim(strtoupper($type));
        $this->flavor = $type;
        $this->type = 'html';
        $this->domain = 'PUBLIC';
        $this->lang = 'EN';
        $this->dtdversion = $version;
        
        if($type=='' && func_num_args()<2)$this->dtdversion = '1.1';
        if($type == 'TRANSITIONAL'||$type == 'LOOSE'){
            if((null ===$version)) $this->dtdversion = '1.0';
            $this->dtdurl='http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd';
            $this->dtd ="-//W3C//DTD XHTML {$this->dtdversion} Transitional//EN";
        }elseif($type == 'FRAMESET'){
            if((null ===$version)) $this->dtdversion = '1.0';
            $this->dtdurl='http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd';
            $this->dtd ="-//W3C//DTD XHTML {$this->dtdversion} Frameset//EN";
        }elseif($type == 'STRICT'){
            if((null ===$version)) $this->dtdversion = '1.0';
            $this->dtdurl='http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd';
            $this->dtd ="-//W3C//DTD XHTML {$this->dtdversion} Strict//EN";
        }elseif($type == 'BASIC'){
            if((null ===$version)) $this->dtdversion = '1.1';
            if($this->dtdversion ==1){
                $this->dtdversion = '1.0';
                $this->dtdurl= 'http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd';
            }else{
                $this->dtdurl='http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd';
            }
            $this->dtd ="-//W3C//DTD XHTML Basic {$this->dtdversion}//EN";
        }else{
            if((null ===$version)) $this->dtdversion = '1.1';
            $this->dtdurl='http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd';
            $this->dtd ="-//W3C//DTD XHTML {$this->dtdversion}//EN";
        }
        return $this;
    }
    public function __call($name, $arguments) {
        if(method_exists($name,$this)){
            if(!isset($arguments[0]))
                return $this->$name();
            else{
                return call_user_func_array(array($this,$name),$arguments);
            }
        }
        $name2 = 'set'.$name;
        if(method_exists($name,$this)){
            return call_user_func_array(array($this,$name2),$arguments);
        }
        $name2 =strtolower($name);
        
        if(property_exists($this,$name2)){
            if(!isset($arguments[0]))
                return $this->$name2;
            else{
                $this->$name2 = $arguments[0];
                return $this;
            }
        }
        return $this;
    }
    public function __toString() {
        $a=array();
        $a[] = $this->type;
        if($this->domain) $a[] = $this->domain;
        if($this->dtd) $a[] = '"' . $this->dtd . '"';
        if($this->dtdurl) $a[] = '"' . $this->dtdurl . '"';
        
        return trim(implode(' ',array_filter($a)));
    }
}
