<?php
namespace Compago\Tools;

class Pipeline {
    protected $layers = [];
    protected $then ;
    protected $i = 0;
    public function layers(Array $layers){
        $this->layers = $layers;
        return $this;
    }
    public function add($mw){
        $this->layers[] = $mw;
        return $this;
    }
    public function then($mw){
        $this->then = $mw;
        return $this;
    }
    public function peel($object, Closure $next){
        
    }
    public function __invoke($object,$next) {
        if (count($this->layers) == 0){
            return $next($object);
        }
        $anonFx = function($object) use (&$anonFx,&$RESPONSE){
            $this->i++;
            if (isset($this->layers[$this->i])){
                return $this->layers[$this->i]($object,$anonFx);
            }
            return $RESPONSE;
        };
        return $this->layers[$this->i]($object,$anonFx);
    }
    
}


            $midWares = array_values($this->middleware);
            if (count($midWares)){
                $i = 0;
                $anonFx = function($REQUEST) use (&$i,&$anonFx,&$RESPONSE,&$midWares){
                    $i++;
                    if (isset($midWares[$i])){
                        return $midWares[$i]($REQUEST,$anonFx);
                    }
                    return $RESPONSE;
                };
                $RESPONSE = $midWares[$i]($REQUEST,$anonFx);
            }