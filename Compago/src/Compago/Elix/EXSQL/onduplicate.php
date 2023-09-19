<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;
include_once('set.php');
class onduplicate extends set {
    public function __toString() {
        $w = $this->raw();
        if($w)
            return "ON DUPLICATE KEY UPDATE $w";
        else
            return '';
        
    }
}

?>