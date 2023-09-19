<?php
namespace App\Middleware;
use Compago\Middleware;

class AfterMiddleware extends Middleware{
    public function __invoke($REQUEST,$next) {
        __er('called ' . __CLASS__);
        return $next($REQUEST);
    }
}
