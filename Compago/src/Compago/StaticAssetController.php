<?php
namespace Compago;

class StaticAssetController {
    protected $root = '';
    protected $debug = 1;
    public function __construct($root = '.', $debug=1)
    {
        $root = rtrim(realpath($root), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->root = $root;
        $this->debug = $debug;
    }
    public function __invoke($REQUEST,$RESPONSE, $matchedRoute) {
        $filename = $this->root;
        $matches = $matchedRoute->getMatchedData();
        
        if (isset($matches['path'])){
            $filename .= $matches['path'];
        } elseif (isset($matches[0])){
            $filename .= $matches[0];
        }
        if (file_exists($filename)){
            return new \Compago\Http\FileResponse($filename);
        }
        if ($this->debug){ __Er("StaticAssetController File not found: $filename");}
        if ($this->debug == 2){ __Er($matchedRoute);}
        
        return new \Compago\Http\Response('File not found', 404);
    }
}
