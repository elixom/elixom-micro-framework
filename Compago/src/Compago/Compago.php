<?php
namespace Compago;

class Compago {
    public static function helpers($type){
        $type = ucfirst($type);
        $file = __DIR__  .DIRECTORY_SEPARATOR . "Helpers" . DIRECTORY_SEPARATOR . $type . '.php';
        if (file_exists($file)){
            include_once($file);
        }
        
    }
}

    