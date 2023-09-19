<?php
namespace Compago\Tools\Password;

/**
 * 
 *  $hpwd = new Compago\Tools\Password\HashedPassword('hashed-password');
 * $hpwd->needs_rehash()
 *      $hpwd->new_hash('plain-password');
 *      $hpwd->hash;
 *      $hpwd->verify('plain-password');
 * 
*/
include_once('password-compat.inc');


class HashedPassword {
    protected $data = array();
    protected $hash = null;
    protected $plain = null;
    public function __construct($hash,$plain='') {
        $this->hash = $hash;
        $this->plain = $plain;
        $this->data = password_get_info($hash);
    }
    public function __toString() {
        return $this->hash;
    }
    public function __get($name) {
        if ($name == 'hash'){
            return $this->hash;
        } 
        if ($name == 'plain'){
            return $this->plain;
        } 
        if(method_exists($this,$name)){
            return $this->$name();
        }
    }

    public function toString() {
        return $this->hash;
    }
    public function id(){
        return $this->hash;
    }
    
    public function info(){
        return $this->data;
    }
    public function needs_rehash(){
        if(substr($this->hash,0,1) != '$'){
            return true;
        }
        return password_needs_rehash($this->hash,PASSWORD_BCRYPT);
    }
    public function verify($plain){
        if(substr($this->hash,0,1) != '$'){
            return $this->hash == md5($plain);
        }
        return password_verify($plain,$this->hash);
    }
    public function new_hash($plain){
        if($this->verify($plain)){
            if(password_needs_rehash($this->hash,PASSWORD_BCRYPT)){
                $hashed = password_hash($plain,PASSWORD_BCRYPT,$this->data);
                if($hashed === FALSE){
                    $hashed = md5($plain);
                }
                return $hashed;
            }
            return '';
        }
        return false;
    }
}