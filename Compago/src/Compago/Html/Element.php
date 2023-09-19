<?php
/**
 * @author Shane Edwards
 * @copyright 2018
 */
namespace Compago\Html;
class Element extends Node{
    public function __call($name, $arguments) {
        $lname = strtolower($name);
        $n = count($arguments);
        $tag = $this->tagName();
        if($lname=='options') {
            if ($tag == 'select'){
                return $this->nodes;
            }
            if ($tag == 'optgroup'){
                return $this->nodes;
            }
            if ($tag == 'datalist'){
                return $this->nodes;
            }
            if ($tag == 'radio-group'){
                return $this->nodes;
            }
            if ($tag == 'checkbox-group'){
                return $this->nodes;
            }
        }
        if($lname=='cells') {
            if ($tag == 'tr'){
                return $this->nodes;
            }
        }
        if($lname=='rows') {
            if ($tag == 'tbody' || ($tag == 'thead') || ($tag == 'tfoot')){
                return $this->nodes;
            }
        }
        if($lname=='item') {
            if ($tag == 'select' || ($tag == 'optgroup') || ($tag == 'datalist')){
                if($n==0){
                    return $this->nodes;
                }else{
                    return $this->node($arguments[0]);
                }
            }
        }
        if($lname=='type') {
            if ($tag == 'select'){
                return $this->attr('multiple')?'select-multiple':'select-one';
            }
            if ($tag == 'fieldset'){
                return $tag;
            }
        }
        
        if($lname=='length') {
            if ($tag == 'select'){
                //NOT completely accurate. Should reutrn the number of OPTIONS
                return count($this->nodes);
            }
        }
        if($lname=='summary') {
            if ($tag == 'details'){
                $el = null;
                foreach($this->nodes as $node){
                    if (is_node_of($node,$lname)){
                        $el = $node;
                    }
                }
                if ($el === null){
                    $el = HtmlUtils::create($lname);
                    array_unshift($this->nodes,$el);
                }
                if ($n > 0){
                    $el->apppend($arguments[0]);
                }
                return $el;
            }
        }
        if($lname=='legend') {
            if ($tag == 'fieldset'){
                $el = null;
                foreach($this->nodes as $node){
                    if (is_node_of($node,$lname)){
                        $el = $node;
                    }
                }
                if ($el === null){
                    $el = HtmlUtils::create($lname);
                    array_unshift($this->nodes,$el);
                }
                if ($n > 0){
                    $el->apppend($arguments[0]);
                }
                return $el;
            }
        }
        if($lname=='value') {
            if (($tag == 'output') || ($tag == 'textarea')){
                if($n==0){
                    return $this->innerHtml();
                }else{
                    $this->nodes = array();
                    $this->innerHtml($arguments[0]);
                    return $this;
                }
            }
            if (($tag == 'select') || ($tag == 'optgroup')){
                if($n==0)
                {
                    $item =$this->selected();
                    if(count($item)==0) return null;
                    
                    $r=array();
                    foreach($item as $i)
                        $r[]=$i->value();
                    
                    return implode(",",$r); 
                }else
                {
                    $this->selected($arguments[0]);
                    return $this;
                }
            }
        }
        if(($lname=='addrow')|| ($lname=='addtr') || ($lname=='insertrow')|| ($lname=='tr')) {
            if (($tag == 'tbody')||($tag == 'thead')||$tag == 'tfoot'){
                return $this->addChild('tr');
            }
            if ($tag == 'table'){
                return $this->tbody()->addChild('tr');
            }
        }
        if(($lname=='addcol')) {
            if (($tag == 'colgroup')||($tag == 'table')){
                return $this->addChild('col');
            }
        }
        if(($lname=='addth')|| ($lname=='addtd')) {
             $newNodeType = ($lname=='addth')?'th':'td';
             if($n==0){
                return $this->addChild($newNodeType);
            }elseif($n>1)
            {
                $r = array();
                foreach($arguments as $v)
                    $r[] = $this->addChild($newNodeType,$v);
                return $r;
            }elseif(is_array($arguments[0]))
            {
                $r = array();
                foreach($arguments[0] as $v)
                    $r[] = $this->addChild($newNodeType,$v);
                return $r;
            }else{
                return $this->addChild($newNodeType,$arguments[0]);
            }
        }
        if(($lname=='httpequiv')||($lname=='http_equiv')) {
            if ($tag == 'meta'){
                $name = 'http-equiv';
            }
        }
        return parent::__call($name, $arguments);
    }
    public function name_and_id($value=null,$setID=true)
    {
        $n = func_num_args();
        if($n==0){
            return $this->attr('id');
        }else{
            if($n>1){
                $id = $setID;
                if(is_bool($setID) && $setID) 
                    $id = $value;
                elseif(is_string($setID)){ 
                    if($setID!='')$id = $setID;
                    $setID = true;
                }else
                    $id = $setID? $value:'';
                if($setID){
                    $this->id($id);
                }
            }else{
                $this->id($value);
            }
            return $this->attr('name',$value);
        }
    }
    public function html()
    {
         return $this->__toString();
    }
    public function show(){
        $this->style('display','');
        return $this;
    }
    public function hide(){
        $this->style('display','none');
        return $this;
    }
    public function style($value=null)
    {
        $n =func_num_args();
        if($n == 0){
            return $this->attr('style');
        }else
        {
            $sa = array();
            if(!empty($this->attr['style'])){ 
                $s = explode(';',$this->attr['style']);
                $s = array_map('trim',$s);
                $s = array_filter($s);
            }else
                $s = array();
            
            foreach($s as $k=>$v)
            {
                if(strpos($v,':')){
                    list($p,$v) =  explode(':',$v,2);
                    $p =strtolower($p);
                    $v = trim($v);
                }else
                    $v =$p ='';
                if(!empty($p) && !empty($v))
                    $sa[$p] = $v;
            }
            if($n == 1){
                $s = explode(';',$value);
                $s = array_map('trim',$s);
                $s = array_filter($s);
                foreach($s as $k=>$v)
                {
                    if(strpos($v,':')){
                        list($p,$v) =  explode(':',$v,2);
                        $p = strtolower($p);
                        $v = trim($v);
                    }else{
                        $v = $p ='';
                    }
                    if(!empty($p) && !empty($v))
                        $sa[$p] = $v;
                }
            }else{
                $value = strtolower($value);
                $sa[$value] = func_get_arg(1);
            }
            $r = array();
            foreach($sa as $k=>$v)
                if(!empty($v))$r[] = "$k: $v";
            return $this->attr('style',implode(';',$r));
        }
    }
    
    private static function parseStyle($value){
        
        $results = array();
        $value = trim($value);
        $value = trim($value,';');
        if(empty($value)) return $results;
        
        if(stripos($value,'url(') !== false){
            $s = explode(';',$value);
            $l = -1;
            foreach($s as $i => $v){
                if(strpos($v,':') == false){
                    if($l > -1){
                        $s[$l] = $s[$l] . $v;
                        $s[$i] = '';
                    }
                }else{
                    $l =$i;
                }
                $s[$i] = trim($s[$i]);
            }
            $s = array_filter($s);
            foreach($s as $i=>$v)
            {
                if(strpos($v,':')){
                    list($p,$v) =  explode(':',$v,2);
                    $p = strtolower($p);
                    $results[$p] = trim($v);
                }
            }
        }else{
            preg_match_all("/([\w-]+)\s*:\s*([^;]+)\s*;?/", $value, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $results[strtolower($match[1])] = $match[2];
            }
        }
        return $results;
        
            
        /* does not work with
         background-image: url(/example.jpeg?arg=1;arg=2);
         TEST string:
         
         color:#777;background-image: url(/example.jpeg?arg=1;arg=2);font-size:16px;font-weight:bold;left:214px  ; position:relative; top:   70px
        */
            
    }
    public function addStyle($value=null){
        if(func_num_args()){
            if(empty($this->attr['style'])){
                $c = array();
            }else{
                $c = self::parseStyle($this->attr['style']);
            }
            $a = func_get_args();
            if(count($a) == 1){
                if(is_array($value)) $value = implode(';',$value);
            }else{
                $a = array_filter($a);
                $value = implode(';',$a);
            }
            
            $n = self::parseStyle($value);
            $a = array_merge($c,$n);
            
            $r = array();
            foreach($a as $k=>$v)
                if(!empty($v))$r[] = "$k: $v";
                
            $this->attr['style'] = implode('; ', $r);
            
        }elseif(!isset($this->attr['style'])){
            $this->attr['style'] = '';
        }
        return $this;
    }
    public function addClass($class=''){
        if(func_num_args()){
            $a = func_get_args();
            $a = array_filter($a);
            
            if(empty($this->attr['class'])){
                $r = $a;
            }else{
                $r = array_merge(array($this->attr['class']),$a);
            }
            $r = explode(' ',implode(' ',$r));
            $r = array_filter(array_unique($r));
            $this->attr['class'] = implode(' ',$r);
        }elseif(!isset($this->attr['class'])){
            $this->attr['class'] = '';
        }
        return $this;
    }
    
    public function setClass($class='',$replace=true)
    {
        if($replace)
        {
            return $this->attr('class',$class);
        }
        else
        {
            return $this->addClass($class);
        }
    }
    public function removeClass($class=''){
        if(func_num_args()){
            if(!empty($this->attr['class'])){
                $r = explode(' ',$this->attr['class']);
                $a = func_get_args();
                $a = explode(' ',implode(' ',$a));
                $a = array_filter($a);
                
                $r = array_diff($r, $a);
                $r = array_unique($r);
                $this->attr['class'] = implode(' ',$r); 
            }
        }else{
            unset($this->attr['class']);
        }
        return $this;
    }
    public function src($url='')
    {
        if(func_num_args()){
            $a = func_get_args();
            $url = implode('/',$a);
            return $this->attr(__FUNCTION__,$url);
        }
        return $this->attr(__FUNCTION__);
    }
    public function href($url='')
    {
        if(func_num_args()){
            $a = func_get_args();
            $url = implode('/',$a);
            return $this->attr(__FUNCTION__,$url);
        }
        return $this->attr(__FUNCTION__);
    }
    
    public function formenctype($value=null)
    {
        if(func_num_args()==0)
            return $this->attr(__FUNCTION__);
        else{
            $l=strtolower($value);
            if(in_array($l,array('text/plain','multipart/form-data','application/x-www-form-urlencoded'))){
                $this->attr[__FUNCTION__] = $l;
                return $this;
            }elseif(empty($value))
                return $this->attr(__FUNCTION__,$value);
        }
    }
    public function target($value=null)
    {
        if(func_num_args()==0)
            return $this->attr(__FUNCTION__);
        else{
            $l=strtolower($value);
            if(in_array($l,array('_blank','_self','_parent','_top')))
            {
                $this->attr[__FUNCTION__] = $l;
                return $this;
            }else
                return $this->attr(__FUNCTION__,$value);
        }
    }
    public function formtarget($value=null)
    {
        if(func_num_args()==0)
            return $this->attr(__FUNCTION__);
        else{
            
            $l=strtolower($value);
            if(in_array($l,array('_blank','_self','_parent','_top')))
            {
                $this->attr[__FUNCTION__] = $l;
                return $this;
            }else
                return $this->attr(__FUNCTION__,$value);
        }
    }
    public function formmethod($value=null){
        if(func_num_args()==0){
            return $this->attr(__FUNCTION__);
        }else{
            $value=strtoupper($value);
            if(in_array($value,array('POST','GET')))
                $this->attr(__FUNCTION__,$value);
            
            return $this;
        }
    }
    public function formaction($url=null)
    {
        if(func_num_args()){
            $a = func_get_args();
            $url = implode('/',$a);
            return $this->attr(__FUNCTION__,$url);
        }
        return $this->attr(__FUNCTION__);
    }
    public function selected($value=null)
    {
        $n = func_num_args();
        $tag = $this->tagName();
        if ($tag == 'option'){
            if($n==0)
            {
                if(isset($this->attr[__FUNCTION__]))
                    return true;
                else
                    return false;
            }else{
                if(empty($value)||$value==false)
                    unset($this->attr[__FUNCTION__]);
                else
                    $this->attr[__FUNCTION__] = $value;
                return $this;
            }
        }
        if (($tag == 'select') || ($tag == 'optgroup')){
            $setThem = (func_num_args()>0);
            if($setThem){
                if(!is_array($value)) $value = explode(',',$value);
                $multiple = $this->multiple();
            }else{
                $r = array();
            }
                
            foreach($this->nodes as $item)
            {
                if(!($item instanceof node)){
                    continue;
                }
                if($item->tagName() == 'optgroup')
                {
                    $x = $item->nodes();
                    foreach($x as $it)
                    {
                        if(!($it instanceof node)){
                            continue;
                        }
                        if($setThem){
                            if(in_array($it->value(), $value))
                            {
                                $it->selected(true);
                            }else
                                if(!$multiple)$it->selected(false);
                        }else
                        {
                            if($it->selected())$r[] = $it;
                        }
                    }
                }else
                {
                    if($setThem){
                        if(in_array($item->value(), $value))
                        {
                            $item->selected(true);
                        }else
                            if(!$multiple)$item->selected(false);
                    } else
                    {
                        if($item->selected())
                            $r[] = $item;
                    }
                }
            }
            if($setThem) 
                return $this;
            else
                return $r;
        }
        
        if($n==0){
            return $this->Attr(__FUNCTION__);
        }
        return $this->Attr(__FUNCTION__,$value);
    }
    public function createFragment(){
        if(func_num_args()==1){
            $el = HtmlUtils::createFragment(func_get_arg(0));
        }else{
            $el = HtmlUtils::createFragment();
        }
        $this->nodes[] = $el;
        return $el;
    }
    public function createHidden($name, $value='')
    {
        $el = HtmlUtils::input('hidden',$name);
        $el->value($value);
        $this->nodes[] = $el;
        return $el;
    }
    public function create($nodeType){
        $tag = $this->tagName();
        $nodeType = strtolower($nodeType);
        
        if ($tag == 'tr'){
            if($nodeType=='td' || $nodeType=='th'){
                return parent::create($nodeType);
            }else{
                $el = parent::create('td');
                return $el->create($nodeType);
            }
        }
        if (($tag == 'tbody')||($tag == 'thead')||$tag == 'tfoot'){
            if($nodeType=='tr'){
                return parent::create($nodeType);
            }else{
                $el = parent::create('tr');
                return $el->create($nodeType);
            }
        }
        if ($tag == 'colgroup'){
            $nodeType = 'col';
        }
        return parent::create($nodeType);
    }
    
    public function input($inputType,$name=''){
        $el = HtmlUtils::input($inputType,$name);
        if(func_num_args()>2){
            $value = func_get_arg(2);
            $el->value($value);
        }
        $this->nodes[] = $el;
        return $el;
    }
    /**
     * HTML_element::add()
     *  add a decendent child. 
     *      - if the $node is a HTML element it will be added
     *      - if the $node is scalar a proper decendent will be created with $node as the inner text
     *      - by default decendent the child will be the type of the parent element except where the parent has a defined childType e.g. UL, OL
     * @param mixed $node
     * @return void
     */
    public function add($node=''){
        $tag = $this->tagName();
        if (($tag == 'tbody')||($tag == 'thead')||$tag == 'tfoot'){
            if(is_node_of($node,'tr')){
                $this->nodes[] =$node;
                return $node;
            } else {
                $el = parent::create('tr');
                return $el->append($node);
            }
        }
        if ($tag == 'colgroup'){
            if(is_node_of($node,'col')){
                $this->nodes[] =$node;
                return $node;
            } else {
                $el = parent::create('col');
                return $el->append($node);
            }
        }
        if ($tag == 'tr'){
            if(($node instanceof node) && (($node->tagName() == 'th') || ($node->tagName() == 'td'))){
                $this->nodes[] =$node;
                return $node;
            }else{
                $el = parent::create('td');
                return $el->append($node);
            }
        }
        if ($tag == 'select'){
            if(func_num_args()){
                if(($node instanceof node) && (($node->tagName() == 'option') || ($node->tagName() == 'optgroup'))){
                    $this->nodes[] =$node;
                    return $node;
                }else{
                    return $this->addOption($node);
                }
            }
            return $this->addOption();
        }
        if (($tag == 'optgroup') || ($tag == 'datalist')){
            if(func_num_args()){
                if(is_node_of($node,'option')){
                    $this->nodes[] =$node;
                    return $node;
                }else{
                    return $this->addOption($node);
                }
            }
            return $this->addOption();
        }
        if ($tag == 'dl'){
            //add($dt='',$dd='')
            $a = func_get_args();
            $elt = HtmlUtils::create('dt');
            $eld = HtmlUtils::create('dd');
            $elt->append(isset($a[0])?$a[0]:'');
            $eld->append(isset($a[1])?$a[1]:'');
            $this->nodes[]=$elt;
            $this->nodes[]=$eld;
            return array($elt,$eld);
        }
        
        if ($tag == 'ul' || $tag == 'ol'){
            if(is_array($node)){
                $el = array();
                foreach($node as $item){
                    $el[] = $this->add($item);
                }
                return $el;
            }elseif(is_node_of($node,'li')){
                $el = $node;
            } else{
                $el = HtmlUtils::create('li');
                $el->append($node);
            }
            $this->nodes[] = $el;
            return $el;
        }
        
        if(func_num_args()){
            if($node instanceof node){
                $el = $node;
            }else{
                $c = get_called_class();
                $el = new $c();
                $el->append($node);
            }
        }else{
            $c = get_called_class();
            $el = new $c();
        }
        $this->nodes[] = $el;
        return $el;
    }
    public function addFieldset($legend=''){
        $el = $this->create('fieldset');
        if($legend)$el->legend($legend);
        return $el;
    }
    
    public function addMeta($name='', $content='') {
        $m = $this->create('meta');
        if(func_num_args()) $m->name($name);
        if(func_num_args()>1) $m->content($content);
        return $m;
    }
    public function addLink($href='',$rel='') {
        $m = $this->create('link');
        if(func_num_args()) $m->href($href);
        if(func_num_args()>1) $m->rel($rel);
        return $m;
    }
    public function addScript($href='') {
        $m = $this->create('script');
        if(func_num_args()) $m->src($href);
        return $m;
    }
    public function prepend($value=''){
        
        if(is_array($value)){
            $value = array_reverse($value);
            $el = array();
            foreach($value as $item){
                $el[] = $this->prepend($item);
            }
            return $el;
        }
        $tag = $this->tagName();
        if ($tag == 'ul' || $tag == 'ol'){
            if(is_node_of($value, 'li')){
                $el = $value;
            }else{
                $el = new self('li');
                $el->append($value);
            }
            parent::prepend($el);
            return $el;
        }
        
        if ($tag == 'optgroup' || $tag=='datalist'){
            if(is_node_of($value, 'option')){
                parent::prepend($value);
                return $this;
            }else{
                $el = new self('option');
                if($value) $el->append($value);
                if(func_num_args()>1) $el->value(func_get_arg(1));
                if(func_num_args()>2) $el->selected(func_get_arg(2));
                parent::prepend($el);
                return $el;
            }
        }
        if ($tag == 'select'){
            if(($value instanceof node) && (($value->tagName() == 'option') || ($value->tagName() == 'optgroup'))){
                parent::prepend($value);
                return $this;
            }else{
                $el = new self('option');
                if($value) $el->append($value);
                if(func_num_args()>1) $el->value(func_get_arg(1));
                if(func_num_args()>2) $el->selected(func_get_arg(2));
                parent::prepend($el);
                return $el;
            }
        }
        if ($tag != 'script'){
            if(strlen($value) && (trim($value)=='')){
                $value = str_replace(' ','&nbsp;',$value);
            }    
        }
        parent::prepend($value);
        return $this;
    }
    public function append($value='')
    {
        if(is_array($value)){
            $el = array();
            foreach($value as $item){
                $el[] = $this->append($item);
            }
            return $el;
        }
        
        $tag = $this->tagName();
        if (($tag == 'tbody')||($tag == 'thead')||$tag == 'tfoot'){
            if(is_node_of($value, 'tr')){
                $this->nodes[] =$value;
            } else {
                $el = parent::create('tr');
            }
            return $this;
        }
        if ($tag == 'tr'){
            if(($value instanceof node) && (($value->tagName() == 'td') || ($value->tagName() == 'th'))){
                $this->nodes[] =$value;
            }else{
                $el = new self('td');
                $el->append($value);
                $this->nodes[] =$el;
            }
            return $this;
        }
        if ($tag == 'ul' || $tag == 'ol'){
            if(($value instanceof node) && ($value->tagName() == 'li')){
                $el = $value;
            }else{
                $el = new self('li');
                $el->append($value);
            }
            $this->nodes[]=$el;
            return $el;
        }
        if ($tag == 'pre'){
            if(is_scalar($value)){
                $this->nodes[] = $value;
            } else{
                $this->nodes[] = print_r($value,1);
            }
            return $this;
        }
        if ($tag == 'optgroup' || $tag=='datalist'){
            if(is_node_of($value, 'option')){
                $this->nodes[] =$value;
                return $this;
            }else{
                $el = new self('option');
                if($value) $el->append($value);
                if(func_num_args()>1) $el->value(func_get_arg(1));
                if(func_num_args()>2) $el->selected(func_get_arg(2));
                $this->nodes[] =$el;
                return $el;
            }
        }
        if ($tag == 'select'){
            if(($value instanceof node) && (($value->tagName() == 'option') || ($value->tagName() == 'optgroup'))){
                $this->nodes[] =$value;
                return $this;
            }else{
                $el = new self('option');
                if($value) $el->append($value);
                if(func_num_args()>1) $el->value(func_get_arg(1));
                if(func_num_args()>2) $el->selected(func_get_arg(2));
                $this->nodes[] =$el;
                return $el;
            }
        }
        parent::append($value);
        return $this;
    }
    
    public function addOptions(Array $set, $useSetKeys=HTML_OPT_VALUEKEYS)
    {
        if(func_num_args()==2 && is_bool($useSetKeys)){
            $useSetKeys = $useSetKeys?HTML_OPT_VALUEKEYS:HTML_OPT_VALUELABEL;
        }
        if ($this->tagName() == 'datalist' && (func_num_args()==1)){
            $useSetKeys = HTML_OPT_VALUENONE;
        }
        $r = array();
        foreach($set as $k=>$label)
        {
            if($useSetKeys==HTML_OPT_VALUEKEYS){
                if (is_array($label)){
                    $og = $this->addOptGroup($k);
                    $og->addOptions($label,$useSetKeys);
                    $r[] = $og;
                } else{
                    $r[] = $this->addOption($label,$k);
                }
            }elseif($useSetKeys==HTML_OPT_VALUELABEL){
                $r[] = $this->addOption($label,$label);
            }else{
                $r[] = $this->addOption($label);
            }
        }
        return $r;
    }
    public function addOption($label=''/*,$value=null,$selected=false,$atTop=false*/)
    {
        
        $el = new self('option');
        if($label){
            $el->append($label);
        }
        $n = func_num_args();
        if($n>1){
             $el->value(func_get_arg(1));
        }
        if($n>2){
             $el->selected(func_get_arg(2));
        }
        $atTop= ($n>3)?func_get_arg(3):false; 
        if($atTop){
            array_unshift($this->nodes,$el);
        }else{
            $this->nodes[] =$el;
        }
        return $el;
    }
    public function addOptionAtTop($label='',$value=null,$selected=false) 
    {
        return $this->addOption($label,$value,$selected,true);
    }
    public function addOptionGroup($label){
        return $this->addOptGroup($label);
    }
    public function addOptGroup($label) 
    {
        $el = new self('optgroup');
        $el->label($label);
        $this->nodes[] =$el;
        return $el;
    }
    public function addChild($newNodeType)
    {
        $newNodeType = strtolower($newNodeType);
        $tag = $this->tagName();
        if ($tag == 'tr'){
            if (!in_array($newNodeType,['td','th'])){
                $newNodeType = 'td';
            }
        }
        if (($tag == 'tbody')||($tag == 'thead')||$tag == 'tfoot'){
            $newNodeType = 'tr';
        }
        if ($tag == 'select'){
            if (!in_array($newNodeType,['option','optgroup'])){
                $newNodeType = 'option';
            }
        }
        if ($tag == 'table'){
            if (!in_array($newNodeType,['tbody','thead','tfoot','caption','colgroup'])){
                $newNodeType = 'tbody';
            }
        }
        if ($tag == 'optgroup' || ($tag == 'datalist')){
            $newNodeType = 'option';
        }
        if ($tag == 'colgroup'){
            $newNodeType = 'col';
        }
        if (empty($newNodeType)){
            //TODO throw error
        }
        $el = new self($newNodeType);
        if(func_num_args()>1){
            $el->innerHtml(func_get_arg(1));
        }
        $this->nodes[]=$el;
        return $el;
    }
    
    public function hasValue($value=null)
    {
        foreach($this->nodes as $item)
        {
            if(($item instanceof node) && ($item->tagName() == 'optgroup')){
                $x = $item->nodes();
                foreach($x as $it)
                {
                    if($it->value() == $value) return true;
                }
            }elseif($value instanceof node)
            {
                if($item->value() == $value) return true;
            }
        }
        return false;
    }
    public function defaultValue($value=null)
    {
        if(func_num_args()==0)
        {
            $r=array();
            $item =$this->selected();
            if(count($item)){
                foreach($item as $i)
                    $r[]=$i->value();
            }else{
                if(count($this->nodes)){
                    $item = $this->nodes[0];
                    if(($item instanceof node) && ($item->tagName() == 'optgroup')){
                        $x = $item->nodes();
                        if(count($x)) 
                            $item = $x[0];
                        else
                            return null;
                    }
                    $r[]=$item->value();
                }else
                    return null;
            }
            return implode(",",$r); 
        }else
        {
            $this->selected($value);
            return $this;
        }
    }
}
class comment extends element
{
    protected $tag = '!--';
    protected $nodes  = array();
    public $inScript = false;
    public function innerHTML($value = null)
    {
        if(func_num_args()==0){
            $r = array();
            foreach($this->nodes as $i)
                $r[] = (string)$i;
            $temp = implode('',$r);
            return str_replace(array('--','>'),array('-- ',' >'),$temp);
        }
        if(is_array($value))
            $this->nodes = $value;
        else 
            $this->nodes = array($value);
        return $this;
    }
    public function getOpenTag()
    {
        return "<!--";
    }
    public function getCloseTag()
    {
        if($this->inScript)
            return " //-->";//to prevent script problem
        else
            return "-->";
    }
}
class form extends element
{
    const ENCODING_PLAIN = 'text/plain';
    const ENCODING_FORMDATA = 'multipart/form-data';
    const ENCODING_FILEDATA = 'multipart/form-data';
    const ENCODING_URLENCODE = 'application/x-www-form-urlencoded';
    
    protected $tag = 'form';
    public function action($url='')
    {
        if(func_num_args()){
            $a = func_get_args();
            $url = implode('/',$a);
            return $this->attr(__FUNCTION__,$url);
        }
        return $this->attr(__FUNCTION__);
    }
    public function enctype($value='application/x-www-form-urlencoded'){
        if(func_num_args()==0){
            return $this->attr('enctype');
        }else{
            $value=strtolower($value);
            if(in_array($value,array('text/plain','multipart/form-data','application/x-www-form-urlencoded')))
                $this->attr('enctype',$value);
            if($value == 'multipart/form-data'){
                $this->method('POST');
            }
            return $this;
        }
    }
    public function method($value='POST'){
        if(func_num_args()==0){
            return $this->attr('method');
        }else{
            $value=strtoupper($value);
            if(in_array($value,array('POST','GET')))
                $this->attr('method',$value);
            
            return $this;
        }
    }
}
class head extends element
{
    protected $tag = 'head';
    protected $charset = '';
    
    public function charset($charset='') {
        if(func_num_args()){
            $this->charset = (string)$charset;
            return $this;
        }
        return $this->charset;
    }
    public function innerHTML($value = null)
    {
        if(func_num_args()==0){
            $r = array();
            if($this->charset){
                $r[] = "<meta charset='$this->charset' />";
            }
            foreach($this->nodes as $i)
                $r[] = (string)$i;
            
            return trim(implode(HtmlUtils::getSeparator($this->tag),$r));
        }
        if(is_array($value))
            $this->nodes = $value;
        else 
            $this->nodes = array($value);
        return $this;
    }
}
class fragment extends element
{
    protected $tag = '';
    public function getOpenTag()
    {
        $a = $this->getAttributes();
        if(empty($a)){
            return '';
        }else{
            $a = str_replace(array('--','>'),array('-- ',' >'),$a);
            return "<!-- $a -->";
        }
    }
    public function getCloseTag()
    {
        return '';
    }
}

