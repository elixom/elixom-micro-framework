<?php
namespace Compago;
use Compago\Http\Response;
use Compago\Http\ResponseInterface;

class Middleware {
    private $response;
    public function __construct(ResponseInterface $response = null) {
        if ($response == null){
            $response = new Response();
        }
        $this->response = $response;
    }
    public function __invoke($REQUEST,$next) {
        
    }
}
