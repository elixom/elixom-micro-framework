<?php
namespace Compago\Tools\Password;

/**
 * 
 *  $hpwd = new Compago\Tools\Password\PlainPassword('hashed-password');
 * 
 *      $ppwd->needs_rehash()
 *      $ppwd->new_hash('plain-password');
 *      $ppwd->hash;
 *      $ppwd->plain;
 *      $ppwd->verify('plain-password');
*/


class PlainPassword extends HashedPassword{
    public function __construct($plain) {
        $hashed = password_hash($plain,PASSWORD_BCRYPT);
        if($hashed === FALSE){
            $hashed = md5($plain);
        }
        $this->hash = $hashed;
        $this->plain = $plain;
        $this->data = password_get_info($hashed);
    }
}