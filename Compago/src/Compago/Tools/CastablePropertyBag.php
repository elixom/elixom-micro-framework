<?php
/**
 * @author Edwards
 * @copyright 2018
 * 
 * 
 */
namespace Compago\Tools;
 
class CastablePropertyBag extends PropertyBag{
    /**
     * Access a request parameter as int
     *
     * @param string    $name     Parameter name
     * @param mixed     $type     Type to cast to
     * @return cast value
     * 
     * int, integer - cast to integer
        bool, boolean - cast to boolean
        float, double, real - cast to float
        string    - cast to string
        array -  cast to array
        object - cast to object
        null,unset - cast to NULL (PHP 5)
     */
    public function cast($name, $type){
        $name = strtolower($name);
        $type = strtolower($type);
        $value =$this->read($name,null);
        if ($type=='boolean') $type ='bool';
        if ($type=='double') $type ='float';
        if ($type=='real') $type ='float';
        if ($type=='unset') $type ='null';
        
        if ($type=='bool' && is_string($value)){
            $value = substr(trim(strtolower($value)),0,4);
            $this->data[$name] = in_array($value,array('on','true','yes'));
        }else if (settype($value,$type)){
            $this->data[$name] = $value;
        }
        return $this->read($name,null);
    }

    /**
     * Access a request parameter as int
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set or is an array
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return int
     */
    public function int($name, $default = 0, $nonempty = false){
        $name = strtolower($name);
        if (!isset($this->data[$name])) return $default;
        if (is_array($this->data[$name])) return $default;
        if ($nonempty && empty($this->data[$name])) return $default;

        return (int) $this->data[$name];
    }

    /**
     * Access a request parameter as string
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set or is an array
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return string
     */
    public function str($name, $default = '', $nonempty = false){
        $name = strtolower($name);
        if (!isset($this->data[$name])) return $default;
        if (is_array($this->data[$name])) return $default;
        if ($nonempty && empty($this->data[$name])) return $default;

        return (string) $this->data[$name];
    }
    

    /**
     * Access a request parameter as bool
     *
     * Note: $nonempty is here for interface consistency and makes not much sense for booleans
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return bool
     */
    public function bool($name, $default = false, $nonempty = false){
        $name = strtolower($name);
        if (!isset($this->data[$name])) return $default;
        if (is_array($this->data[$name])) return true;
        if ($nonempty && empty($this->data[$name])) return $default;
        
        return filter_var($this->data[$name], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Access a request parameter as array
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return array
     */
    public function arr($name, $default = array(), $nonempty = false){
        $name = strtolower($name);
        if (!isset($this->data[$name])) return $default;
        if (!is_array($this->data[$name])) return $default;
        if ($nonempty && empty($this->data[$name])) return $default;

        return (array) $this->data[$name];
    }

}
