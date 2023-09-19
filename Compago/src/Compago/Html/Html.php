<?php
/**
 * @author Edwards
 * @copyright 2013
 * @version 20150104
 */
namespace Compago\Html;

class Html extends Node
{
    protected $tag = 'html';
    protected $head ;
    protected $body ;
    public $doctype = 'html';
    public function docType($dtype = 'html'){
        if (func_num_args()){
            $this->doctype = new Doctype($dtype);
        }
        return $this->doctype;
    }
    public function __construct() {
        $this->head = HtmlUtils::create('head');
        $this->nodes[] = $this->head;
        $this->body = HtmlUtils::create('body');
        $this->nodes[] = $this->body;
    }
    public function head() {
        return $this->head ;
    }
    public function body() {
        return $this->body;
    }
    public function create($tagName)
    {
        $tagName = strtolower($tagName);
        if($tagName=='head') return $this->head;
        if($tagName=='body') return $this->body;
        if(in_array($tagName, array('link','title','base','meta','script','style'))){
            return $this->head->create($tagName);
        }
        $el = HtmlUtils::create($tagName);
        //$el->parent($this);
        $this->nodes[] = $el;
        return $el;
    }
    public function append($innerHTML=''){
        return $this->body->append($innerHTML);
    }
    public function prepend($innerHTML=''){
        return $this->body->append($innerHTML);
    }
    public function __toString() {
        $r = array();
        $d = (empty($this->doctype))?'':' ' . trim($this->doctype);
        $r[] = "<!DOCTYPE{$d}>";
        $r[] = $this->getOpenTag();
        $r[] = $this->innerHTML();
        $r[] = '</html>';
        return implode("\n",$r);
    }
}