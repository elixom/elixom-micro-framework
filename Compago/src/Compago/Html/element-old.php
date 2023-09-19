<?php
/**
 * @author Edwards
 * @copyright 2012
 * @version 20161026
 */

class HTML_element
{
    
    protected $_join_with ='';
    protected $_inner_join_with ='';
    public function __construct() {
        if(func_num_args() && func_get_arg(0)){
            $param = func_get_arg(0);
            if($param instanceof self )
                $this->parent = $param;
            elseif(is_scalar($param)){
                if(method_exists($this,'name')){
                    $this->name($param);
                }else{
                    $this->id($param);
                }
            }
            //a second element should be the innerHtml
        }
    }
    
    
    
    
    public function parent()
    {
        if(func_num_args()){
            if(func_get_arg(0) instanceof self )
                $this->parent = func_get_arg(0);
            return $this;
        }else{
            $key = __FUNCTION__;
            return $this->$key;
        }
    }
    
    public function _BOOTSTRAP($object)
    {
        include_once('_BOOTSTRAP.php');
        $value = _BOOTSTRAP::build($object);
        $this->nodes[] = $value;
        return $value;
    }
    public function _STYLESHEET($href='')
    {
        $value = HTML::build('link');
        $value->rel('stylesheet');
        if(func_num_args()) $value->href($href);
        $this->nodes[] = $value;
        return $value;
    }
    
    
    
    
    
    
    
    
    
    
}
