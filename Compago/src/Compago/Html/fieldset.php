<?php
/**
 * @author Edwards
 * @copyright 2010
 * @version 20130504.1
 */
 
class HTML_fieldset extends HTML_element
{
    protected $tag = 'fieldset';
    public function createGroup($label=''){
        $el = HTML::build('formgroup');
        if(func_num_args()){
            $el->label()->append($label);
        }
        $this->nodes[] = $el;
        return $el;
    }
}

?>