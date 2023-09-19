<?php
/**
 * @author Edwards
 * @copyright 2016
 * 
 * $PML = ELIX::password();
 * $hpwd = $PML->get('hashed-password');
 *      $hpwd->needs_rehash()
 *      $hpwd->new_hash('plain-password');
 *      $hpwd->hash;
 *      $hpwd->verify('plain-password');
 * $ppwd = $PML->getPlain('plain-password');
 *      $hpwd->needs_rehash()
 *      $hpwd->new_hash('plain-password');
 *      $hpwd->hash;
 *      $hpwd->plain;
 *      $hpwd->verify('plain-password');
 */

namespace ELIX;
class PassWord extends \Compago\Tools\Password{
    public function get($hashed){
        return new \Compago\Tools\Password\HashedPassword($hashed);
    }
    public function getPlain($plain){
        return new \Compago\Tools\Password\PlainPassword($plain);
    }
    public function getInspector($plain){
        return new \Compago\Tools\Password\Inspector($plain);
    }
    /*public function generate($length = 6,$prefix=''){
        return Compago\Tools\Password::generate($length,$prefix);
    }
    public function hash($plain){
        return Compago\Tools\Password::hash($plain);
    }
    
    public function verify($plain,$hashed){
        return Compago\Tools\Password::verify($plain,$hashed);
    }
    public function info($hashed){
        return password_get_info($hashed);
    }
    public function needs_rehash($hashed){
        if(substr($hashed,0,1) != '$'){
            return true;
        }
        return password_needs_rehash($hashed,PASSWORD_BCRYPT);
    }*/
}
class PassWord_item extends \Compago\Tools\Password\PlainPassword{}
class PassWord_inspector extends \Compago\Tools\Password\Inspector{}