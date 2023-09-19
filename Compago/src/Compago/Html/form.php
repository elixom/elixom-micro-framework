<?php
/**
 * @author Edwards
 * @copyright 2010
 * @version 20130504.1
 */
HTML::loadapi('input');
class HTML_form extends HTML_element_nameable
{
    
    public function createGroup($label=''){
        $el = HTML::build('formgroup');
        if(func_num_args()){
            $el->label()->append($label);
        }
        $this->nodes[] = $el;
        return $el;
    }
}