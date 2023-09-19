<?php

namespace Compago\Database;

class ConnectionError extends \Exception 
{
    public function __construct($message='',$code=0,$previous=null) {
        $this->message=$message;
        $this->code=$code;
        if($previous instanceof \Exception){
            $this->severity=$previous->getSeverity();
            $this->previous = $previous;
        }elseif(is_int($previous)){
            $this->severity=$previous;
        }else{
            $this->severity = E_USER_ERROR;
        }
        parent::__construct($message, $code, $previous);
    }
    
}