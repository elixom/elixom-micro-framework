<?php
/**
 * @author Edwards
 * @copyright 2013
 * @version 20150104
 */
namespace Compago\Html;
include_once(__DIR__ . DIRECTORY_SEPARATOR .'Constants.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR .'Node.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR .'Element.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR .'Input.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR .'Doctype.php');


class HtmlUtils 
{
    static $HTML_VERSION = 5;
    const VERSION = 3;
    static function build($tagName,$name=''){
        return self::create($tagName,$name);
    }
    static function create($tagName,$name='')
    {
        $tagName=strtolower(trim($tagName));
        if ($tagName == 'comment'){
            return new comment();
        }
        if ($tagName == 'form'){
            return new form();
        }
        if ($tagName == 'head'){
            return new head();
        }
        if ($tagName == 'table'){
            return new Table();
        }
        if ($tagName == 'xdemo'){
            $el = new Element('pre');
            $el->addClass('demo');
            if ($name){
                if (is_array($name)){
                    $el->attr($name);
                } else {
                    $el->id($name);
                }
            }
            return $el;
        }
        
        static $input = array(/*'button',*/'checkbox','color','date','datetime','datetimelocal','datetime-local','email','file','hidden','image','month','number','password','radio','range','reset','search','submit','tel','text'/*,'time'*/,'url','week','year');
        if(in_array($tagName,$input)){
            return self::input($tagName,$name);
        }
        if($tagName=='checkbox-group' || $tagName=='radio-group'){
            return self::input($tagName,$name);
        }
        if($tagName=='input-time' || $tagName=='inputtime'){
            return self::input('time',$name);
        }
        if($tagName=='input-button' || $tagName=='inputbutton'){
            return self::input('button',$name);
        }
        if($tagName=='submit-button' || $tagName=='submitbutton'){
            $el = self::create('button');
            $el->type('submit');
            if($name) $el->name($name);
            return $el;
        }
        
         
        $attrClass ='';
        if(!preg_match('/^[a-z][a-z0-9]*$/i',$tagName)){
            $attrClass = $tagName;
            $tagName = 'div';
        }
        $el =  new element($tagName);
        if($name){
            if (is_array($name)){
                $el->attr($name);
            } else {
                $el->id($name);
            }
        }
        if($attrClass){
            $el->addclass($attrClass);
        }
        if(func_num_args()==3){
            $value = func_get_arg(2);
            $el->append($value);
        }
        return $el;
    }
    
    static function input($type,$name='')
    {
        $object=strtolower($type);
        if(in_array($object,array('select','textarea','output'))){
            $el = self::create($object);
            if (is_array($name)){
                $el->attr($name);
            } else {
                $el->name($name);
            }
            if(func_num_args()>2){
                $value = func_get_arg(2);
                $el->value($value);
            }
            return $el;
        }
        if($object=='datetime') $type = 'datetime-local';
        if($object=='datetimelocal') $type = 'datetime-local';
        
        $el =  new input($type);
        if($name){
            if (is_array($name)){
                $el->attr($name);
            } else {
                $el->name($name);
            }
        }
        if(func_num_args()>2){
            $value = func_get_arg(2);
            $el->value($value);
        }
        return $el;
    }
    static function createFragment($name=''){
        $el = new fragment();
        if ($name){
            $el->id($name);
        }
        return $el;
    }
    static function buildFragment($name='')
    {
        return self::createFragment($name);
    }
    static function isInputType($object){
        if(self::$HTML_VERSION>=5)
            $input = array('button','checkbox','color','date','datetime','datetimelocal',
            'datetime-local','email','file','hidden','image','month','number','password',
            'radio','range','reset','search','submit','tel','text','time','url',
            'week','year');
        else
            $input = array('button','checkbox','file','hidden','image','password','radio',
            'reset','submit','text','year');
        
        return in_array($object,$input);
    }
    
    static function getPattern(){
        static $cg = null;
        if($cg===null){
            $cg = new \Compago\Html\html_pattern();
        }
        return $cg;
    }
    static function getConstants(){
        static $cg = null;
        if($cg===null){
            $cg = new \Compago\Html\Constants();
        }
        return $cg;
    }
    static function getSeparator($tagName){
        if (in_array($tagName,array('body','head','ul','ol','script','style',
                'tbody','thead','tfoot','tr','colgroup','table'))){
            return "\n";
        }
        return '';
    }
}