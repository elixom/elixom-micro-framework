<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;
include_once('where.php');

class having extends where {
    public function __toString() {
        $w = $this->raw();
        if($w)
            return "HAVING $w";
        else
            return '';
        
    }
}

?>