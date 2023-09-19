<?php
/**
 * @author Edwards
 * @copyright 2012
 */
namespace EXSQL;

class limit {
    private $offset = 0;
    private $items = 0;
    
    public function offset($value=0) {
        if(func_num_args()) $this->offset = abs((INT)$value);
        return $this->offset;
    }
    public function items($value=0) {
        if(func_num_args())  $this->items = abs((INT)$value);
        return $this->items;
    }
    public function row_count($value=0) {
        return $this->items($value);
    }
    
    
    public function __construct() {
    }
    
    public function raw($name) {
        if($this->offset && $this->items){
            return "{$this->offset},{$this->items}";
        }elseif($this->items){
            return "{$this->items}";
        }elseif($this->offset ){
            return "{$this->offset}";
        }else
            return '';
    }
    public function __toString() {
        $w = $this->raw();
        if($w)
            return "LIMIT $w";
        else
            return '';
        
    }
}

?>