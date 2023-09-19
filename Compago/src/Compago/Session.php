<?php
namespace Compago{
    @session_start();
    class Session{
        public function group($key){
            return new self($key);
        }
        protected $key = '';
        public function __construct($key = ''){
            $this->key = $key;
            if ($this->key){
                $this->key .= '-';
            }
        }
        public function __get($name) {
            $name = $this->key . strtolower($name);
            if(isset($_SESSION[$name])){
                return $_SESSION[$name];
            }
            return null;
        }
        public function get($name,$default=null) {
            $v = $this->__get($name);
            if ($v === null){
                return $default;
            }
            return $v;
        }
        public function increment($name,$default=0) {
            $v = $this->__get($name);
            if ($v === null){
                $v = $default;
            }
            $v = (int)$v;
            $this->set($name,$v+1);
            return $v;
        }
        public function __set($name, $value) {
            $this->set($name,$value);
        }
        public function set($name, $value) {
            $name = $this->key . strtolower($name);
            if ($value === null){
                unset($_SESSION[$name]);
            } else {
                $_SESSION[$name] = $value;
            }
        }
        public function toArray() {
            if (!$this->key){
                return $_SESSION;
            }
            $name = $this->key;
            $l = strlen($name);
            $r = [];
            foreach($_SESSION as $k=>$v){
                if (substr($k,0,$l) == $name){
                    $r[substr($k,$l)] = $v;
                }
            }
            return $r;
        }
    }
    class SessionItem extends Session{
        //really deprecated
        public function __construct($key = ''){
            throw new \Exception('SessionItem is deprecated');
        }
    }
}


namespace {
    if (! function_exists('session')) {
        function session(){
            static $session = null;
            if ($session === null){
                $session =  new Compago\Session;
            }
            return $session;
        }
    }
}