<?php
/**
 * @author Shane Edwards
 * @copyright 2018
 */
namespace Compago\Html;

//DEPRECATED::
defined('HTML_OPT_LEGACYSELECT') OR define('HTML_OPT_LEGACYSELECT',1);

defined('HTML_ENC_PLAIN') OR define('HTML_ENC_PLAIN','text/plain');
defined('HTML_ENC_FORMDATA') OR define('HTML_ENC_FORMDATA','multipart/form-data');
defined('HTML_ENC_URLENCODE') OR define('HTML_ENC_URLENCODE','application/x-www-form-urlencoded');
defined('HTML_ENC_FILEDATA') OR define('HTML_ENC_FILEDATA','multipart/form-data');

defined('HTML_OPT_VALUENONE') OR define('HTML_OPT_VALUENONE',0);
defined('HTML_OPT_VALUEKEYS') OR define('HTML_OPT_VALUEKEYS',1);
defined('HTML_OPT_VALUELABEL') OR define('HTML_OPT_VALUELABEL',2);

class Constants{
    
}
class html_pattern{
    /** Capital letter followed by Letters, apostrophe and hyphen */
    const HTML_PATTERN_NAME = "^[A-Z]+[A-z\&apos;\-]*$";
    /** Capital letter followed by Letters, apostrophe, hyphen and space*/
    const HTML_PATTERN_FULLNAME = "^[A-Z]+[A-z\&apos;\-\s]*$";
    /** Letters, numbers and hyphen */
    const HTML_PATTERN_CODE = '^[A-z\d\-]*$';
    /** Letters followed by Letters, numbers, dot and hyphen */
    const HTML_PATTERN_USERNAME = '^[A-z]+[A-z\d\.\-]*$';
    /** Letters and space*/
    const HTML_PATTERN_APLHA = '^[A-z\s]*$';
    /** Letters  space and numbers */
    const HTML_PATTERN_APLHANUM = '^[A-z\d\s]*$';
    //const HTML_PATTERN_WORD = '^[A-z]*$';
    //const HTML_PATTERN_WORDNUM = '^[\w]*$';
    //const HTML_PATTERN_NAME = '';
    //const HTML_PATTERN_NAME = '';
    //var date = /^(\d{1,2})[\-/](\d{1,2})[\-/](\d{4})$/,
    //time = /^(\d{1,2})\:(\d{1,2})\:(\d{1,2})$/,
	//'unsigned': /^\d+$/,
	//'integer' : /^[\+\-]?\d*$/,
	//'real'    : /^[\+\-]?\d*\.?\d*$/,
	//'email'   : /^[\w-\.]+\@[\w\.-]+\.[a-z]{2,4}$/,
	//'phone'   : /^[\d\.\s\-]+$/,
    public function __get($name) {
        if(method_exists($this,$name)){
            return $this->$name();
        }
        if(property_exists($this,$name)){
            return $this->$name;
        }
        $name =strtoupper($name);
        if(defined("static::$name")){
            return constant("static::$name");
        }
    }
    public function __call($name, $arguments) {
        return $this->__get($name);
    }
    public static function __callStatic($name, $arguments) {
        if(method_exists(__CLASS__,$name)){
            return self::$name();
        }
        $name =strtoupper($name);
        if(defined("static::$name")){
            return constant("static::$name");
        }
    }


}

class x_html_const{
    protected $enc_plain = 'text/plain';
    protected $enc_formdata = 'multipart/form-data';
    protected $enc_urlencode = 'application/x-www-form-urlencoded';
    protected $enc_filedata = 'multipart/form-data';
    
    
    public function __get($name) {
        if(method_exists($this,$name)){
            return $this->$name();
        }
        if(property_exists($this,$name)){
            return $this->$name;
        }
        return null;
    }
    public function __call($name, $arguments) {
        return $this->__get($name);
    }
    public static function __callStatic($name, $arguments) {
        if(method_exists(__CLASS__,$name)){
            return self::$name();
        }
    }
}