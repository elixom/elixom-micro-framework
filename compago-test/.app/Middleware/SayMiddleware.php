<?php
namespace App\Middleware;
use Compago\Middleware;

class SayMiddleware extends Middleware{
    public function __invoke($REQUEST,$next) {
        $RESPONSE =  $next($REQUEST);
        $RESPONSE->setBody('hi');
        return $RESPONSE;
    }
}
