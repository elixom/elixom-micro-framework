<?php

namespace App\Controllers;

class DebugController extends \Compago\Controller
{
    
    public function __invoke($REQUEST,$RESPONSE, $matchRoute) {
        ob_start();
        echo '<pre>MATCH:';
        print_R($matches);
        print_R($REQUEST);
        print_R($_SESSION);
        phpinfo();
        return ltrim(ob_get_clean());
    }
}
