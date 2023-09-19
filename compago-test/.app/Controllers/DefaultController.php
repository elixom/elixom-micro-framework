<?php

namespace App\Controllers;

class DefaultController extends \Compago\Controller
{
    
    public function __invoke($REQUEST, $RESPONSE, $matchRoute) {
        return '<pre>' . print_r(func_get_args(),1);
    }
}
