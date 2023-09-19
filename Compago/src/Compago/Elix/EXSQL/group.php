<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;
include_once('order.php');
class group extends order{
    public $withROLLUP = false;
    public function __toString() {
        $w = $this->raw();
        if($w){
            if($this->withROLLUP)
                return "GROUP BY $w WITH ROLLUP";
            else
                return "GROUP BY $w";
        }
        else
            return '';
        
    }
}