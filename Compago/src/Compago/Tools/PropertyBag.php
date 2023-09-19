<?php
/**
 * @author Edwards
 * @copyright 2018
 * 
 * 
 */
namespace Compago\Tools;
use Compago\Contracts\Arrayable;
use Compago\Contracts\Jsonable;

class PropertyBag implements Arrayable, Jsonable
{
    use \Compago\Traits\PropertyBag;
    
    public function Read($name, $default=false) {
        $name = strtolower($name);
        if(isset($this)){
            if(isset($this->data[$name])||array_key_exists($name,$this->data))
                return $this->data[$name];
            
            return $default;
        }else{
            if(isset(self::$data[$name]))
                return self::$data[$name];
            else
                return $default;
        }
    }
    public function Seek($name, $default='') {
        $name = strtolower($name);
        if(!array_key_exists($name,$this->data)) $this->data[$name] = $default;
        return $this->data[$name];
    }
    public function Assert($name, $default) {
        $name =strtolower($name);
        if(!isset($this->data[$name]) || empty($this->data[$name]))
            $this->data[$name] = $default;
        return $this->data[$name];
    }
    public function has($name){
        $name = strtolower($name);
        return array_key_exists($name,$this->data);
    }
    public function delete($name,$index=null)
    {
        if(func_num_args()==2 && !is_null($index)){
            if(is_array($this->data[$name])){
                if(is_Array($index))
                    foreach($index as $i){unset($this->data[$name][$i]);}
                else
                    unset($this->data[$name][$index]);
            }
        }else{
            unset($this->data[$name]);
        }
    }
    public function toJson($options = 0){
        return json_encode($this->data,$options);
    }
    
}