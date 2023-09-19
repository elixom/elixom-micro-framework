<?php
/**
 * @author Shane Edwards
 * @copyright 2018
 */
namespace Compago\Html;

class Table extends Element{
    protected $tag = 'table';
    public function __call($name, $arguments) {
        $lname = strtolower($name);
        $n = count($arguments);
        
        if(($lname=='tbodies')) {
            $newNodeType = 'tbody';
            $r = array();
            foreach($this->nodes as $nodeIndex => $node){
                if (is_node_of($node,$newNodeType)){
                    $r[] = $node;
                }
            }
            return $r;
        }
        if(($lname=='rows')) {
        //TODO the ordering of this collection must follow standard (thead, tbodies, tfoot)
            $newNodeType = 'tr';
            $r = array();
            foreach($this->nodes as $nodeIndex => $node){
                if (is_node_of($node,'tbody') || is_node_of($node,'thead') || is_node_of($node,'tfoot')){
                    foreach($node->nodes() as $subnode){
                        if (is_node_of($subnode,$newNodeType)){
                            $r[] = $subnode; 
                        }
                    }
                }
            }
            return $r;
        }
        if( ($lname=='createcolgroup')|| ($lname=='addcolgroup') ){
            $newNodeType = 'colgroup';
            $newNode = new element($newNodeType);
            $this->nodes[] = $newNode;
            return $newNode;
        }
        if( ($lname=='createtbody')|| ($lname=='addtbody') ){
            $newNodeType = 'tbody';
            $newNode = new element($newNodeType);
            $this->nodes[] = $newNode;
            return $newNode;
        }
        if( ($lname=='tbody')){
            $newNodeType = 'tbody';
            $newNode = null;
            foreach($this->nodes as $nodeIndex => $node){
                if (is_node_of($node,$newNodeType)){
                    $newNode = $node;
                    //break; //SHOULD return the last one
                }
            }
            if ($newNode === null){
                $newNode = new element($newNodeType);
                $this->nodes[] = $newNode;
            }
            return $newNode;
        }
        if( ($lname=='colgroup')){
            $newNodeType = 'colgroup';
            $newNode = null;
            foreach($this->nodes as $nodeIndex => $node){
                if (is_node_of($node,$newNodeType)){
                    $newNode = $node;
                    //break; //SHOULD return the last one
                }
            }
            if ($newNode === null){
                $newNode = new element($newNodeType);
                $this->nodes[] = $newNode;
            }
            return $newNode;
        }
        if( ($lname=='caption')|| ($lname=='createcaption')|| ($lname=='addcaption') ||
                ($lname=='thead')|| ($lname=='createthead')|| ($lname=='addthead') ||
                ($lname=='tfoot')|| ($lname=='createtfoot')|| ($lname=='addtfoot')
            ) {
            $spliceIndex = -1;
            $spliceLength = 0;
            if (($lname=='caption')|| ($lname=='createcaption') || ($lname=='addcaption')){
                $newNodeType = 'caption';
            } elseif (($lname=='thead')|| ($lname=='createthead')|| ($lname=='addthead')){
                $newNodeType = 'thead';
            } elseif (($lname=='tfoot')|| ($lname=='createtfoot') ||($lname=='addtfoot')){
                $newNodeType = 'tfoot';
            }
            if($n && ($arguments[0] ===null)){
                $lname = 'delete' . $newNodeType;
            }else{
                $newNode = null;
                foreach($this->nodes as $nodeIndex => $node){
                    if (is_node_of($node,$newNodeType)){
                        $newNode = $node;
                        $spliceIndex = $nodeIndex;
                        if (substr($lname,0,6)!='create'){
                            $spliceLength = 1;
                        }
                        break;
                    }
                }
                if ($n && is_node_of($arguments[0],$newNodeType)){
                    $spliceLength = 1;
                    $n = 0;
                }
                if ($newNode === null){
                    if ($spliceIndex == -1){
                        $spliceIndex = 0;
                    }
                    $newNode = new Element($newNodeType);
                    array_splice($this->nodes, $spliceIndex, $spliceLength, [$newNode]);
                }
                if($n){
                    $newNode->innerHtml($arguments[0]);
                }
                return $newNode;
            }
        }
        if(($lname=='deletecaption')|| ($lname=='deletethead')|| ($lname=='deletetfoot')) {
            if ( ($lname=='deletecaption')){
                $newNodeType = 'caption';
            } elseif ( ($lname=='deletethead')){
                $newNodeType = 'thead';
            } elseif (($lname=='deletetfoot')){
                $newNodeType = 'tfoot';
            }
            foreach($this->nodes as $nodeIndex => $node){
                if (is_node_of($node,$newNodeType)){
                    unset($this->nodes[$nodeIndex]);
                    break;
                }
            }
            return $this;
        }
        if(($lname=='deleterow')|| ($lname=='deletetr')) {
            if($n){
                $newNodeIndex = (int)$arguments[0];
            } else{
                $newNodeIndex = -1;
            } 
            if ($newNodeIndex < -1){
                return $this;
            }
            $newNodeType = 'tr';
            $indexer = 0;
            $lastNodeGroup = null;
            $lastNodeIndex = null;
            $r = array();
            foreach($this->nodes as $node){
                if (is_node_of($node,'tbody') || is_node_of($node,'thead') || is_node_of($node,'tfoot')){
                    foreach($node->nodes() as $nodeIndex => $subnode){
                        if (is_node_of($subnode,$newNodeType)){
                            if ($indexer == $newNodeIndex){
                                $newNodeIndex = $indexer;
                                $this->remove($nodeIndex);
                                break;
                            }
                            $lastNodeGroup = $node;
                            $lastNodeIndex = $nodeIndex;
                            $indexer++;
                        }
                    }
                }
            }
            if (($newNodeIndex == -1) && $lastNodeIndex && $lastNodeGroup){
                $lastNodeGroup->remove($lastNodeIndex);
            }
            return $this;
        }
        
        /*
        if(($lname=='addth')|| ($lname=='addtd')) {
             $type = ($lname=='addth')?'th':'td';
             if($n==0){
                return $this->addChild($type);
            }elseif($n>1)
            {
                $r = array();
                foreach($arguments as $v)
                    $r[] = $this->addChild($type,$v);
                return $r;
            }elseif(is_array($arguments[0]))
            {
                $r = array();
                foreach($arguments[0] as $v)
                    $r[] = $this->addChild($type,$v);
                return $r;
            }else
                return $this->addChild($type,$arguments[0]);
        }*/
        return parent::__call($name, $arguments);
    }
    public function create($object)
    {
        $object = strtolower($object);
        IF(in_array($object,array('caption','colgroup','tfoot','thead'))){
            return $this->$object();
        }elseif($object='tbody'){
            return $this->addTBody();
        }elseif($object='tr'){
            return $this->tBody()->create('tr');
        }else{
            return  parent::create($object);
        }
    }
    public function add($node=''){
        if (is_object($node)){
            if (is_node_of($node,'tr')){
                return $this->tbody()->add($node);
            } elseif (is_node_of($node,'td')){
                return $this->tbody()->add($node);
            } elseif (is_node_of($node,'th')){
                return $this->tbody()->add($node);
            } elseif (is_node_of($node,'caption')){
                return $this->caption($node);
            } elseif (is_node_of($node,'colgroup')){
                return $this->append($node);
            } elseif (is_node_of($node,'tbody')){
                return $this->append($node);
            } elseif (is_node_of($node,'thead')){
                return $this->thead($node);
            } elseif (is_node_of($node,'tfoot')){
                return $this->tfoot($node);
            } else {
                return $this->tbody()->add($node);
            }
        }
        return parent::add($node);
    }
    public function innerHTML($value = null)
    {
        $caption = array();
        $colgroups = array();
        $thead = array();
        $tbodies = array();
        $tfoot = array();
        foreach($this->nodes as $node){
            if (is_node_of($node,'caption')){
                $caption[] = (string)$node;
            } elseif (is_node_of($node,'colgroup')){
                $colgroups[] = (string)$node;
            } elseif (is_node_of($node,'thead')){
                $thead[] = (string)$node;
            } elseif (is_node_of($node,'tbody')){
                $tbodies[] = (string)$node;
            } elseif (is_node_of($node,'tfoot')){
                $tfoot[] = (string)$node;
            }
        }
        
        $sep = HtmlUtils::getSeparator($this->tag);
        $r= array();
        $r[] = implode($sep,$caption);
        $r[] = implode($sep,$colgroups);
        $r[] = implode($sep,$thead);
        $r[] = implode($sep,$tbodies);
        $r[] = implode($sep,$tfoot);
        return trim(implode($sep,$r),$sep);
    }
}