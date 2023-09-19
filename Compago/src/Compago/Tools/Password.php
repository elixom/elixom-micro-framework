<?php
namespace Compago\Tools;


/**
 * 
 *  $hashed = Compago\Tools\Password::hash('plain');
 * $hpwd = Compago\Tools\Password::create('plain');
 * $hpwd = Compago\Tools\Password::generate(6);
*/


class Password {
    public static function create($hash) {
        return new \Compago\Tools\Password\HashedPassword($hash);
    }
    public static function hash($plain) {
        return (new \Compago\Tools\Password\PlainPassword($plain))->hash;
    }
    public static function inspect($plain){
        return new \Compago\Tools\Password\Inspector($plain);
    }
    
    public static function generate($length = 6,$prefix=''){
        if($length <= 0){
            $l = strlen($prefix);
            $length = $l +8;
        }
        if($length <5){
            $possible = str_shuffle("23456789bcdfghjkmnpqrstvwxyz");
            $plain = substr($possible,0,$length);
        }else if($length < 9){
            $possible = str_shuffle('84726abcdefghjkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXWZ&#$');
            $plain = substr(uniqid(),0,3);
            $plain .= substr($prefix . $possible,0,$length-3);
        }else{
            $possible = str_shuffle('0123456789abcdefghjkmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXWZ%&#$');
            if(strlen($possible) < $length){
                $possible .= uniqid();
                $possible .= substr(strrev(Time()),0,3);
            }
            
            $plain = substr($prefix.$possible,0,$length);
            $lpossible = strlen($possible)-1;
            $i = strlen($plain);
            while ($i < $length) { 	
                // pick a random character from the possible ones
                $char = substr($possible, mt_rand(0, $lpossible), 1);	
                $plain .= $char;
                $i++;
            }
        }
        $pwd = new \Compago\Tools\Password\PlainPassword($plain);
        return $pwd;
    }
    public static function verify($plain,$hashed){
        if(substr($hashed,0,1) != '$'){
            return $hashed == md5($plain);
        }
        return password_verify($plain,$hashed);
    }
    public static function info($hashed){
        return password_get_info($hashed);
    }
    public static function needs_rehash($hashed){
        if(substr($hashed,0,1) != '$'){
            return true;
        }
        return password_needs_rehash($hashed,PASSWORD_BCRYPT);
    }
    
    /**
     * Check if current PHP version is compatible with the library
     *
     * @return boolean the check result
     */
    public static function check() {
        static $pass = NULL;

        if (is_null($pass)) {
            if (function_exists('crypt')) {
                $hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
                $test = crypt("password", $hash);
                $pass = $test == $hash;
            } else {
                $pass = false;
            }
        }
        return $pass;
    }
    
    
}