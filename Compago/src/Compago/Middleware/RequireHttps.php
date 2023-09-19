<?php
namespace Compago\Middleware;
use Compago\Http\RedirectResponse;

class  RequireHttps{
    public function __invoke($REQUEST,$next) /*: ResponseInterface*/{
        if (!$REQUEST->getUri()->isHttps()){ //is not https
            $uri = $REQUEST->getUri()->withScheme('https');
            return new RedirectResponse($uri,307);
        }
        $RESPONSE = $next($REQUEST);
        $RESPONSE = $RESPONSE->withHeader('Strict-Transport-Security','max-age=86400');
        return $RESPONSE;
    }
    
    private static function httpsRedirect($absolute_root){
        if (empty($absolute_root)){
            $absolute_root = 'https://' .filter_input(INPUT_SERVER, 'HTTP_HOST');
        }
        if (parse_url($absolute_root,PHP_URL_SCHEME) !== 'https'){
            $parsed_url = parse_url($absolute_root);
            $scheme = 'https://'; 
            $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
            $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
            $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
            $absolute_root = "{$scheme}{$host}{$port}{$path}";
        }
        $absolute_root = rtrim($absolute_root,'/');
        $HTTP_UPGRADE_INSECURE_REQUESTS = getenv('HTTP_UPGRADE_INSECURE_REQUESTS');
        
        if ($HTTP_UPGRADE_INSECURE_REQUESTS){
            $l = strlen($absolute_root);
            $SCRIPT_URI = filter_input(INPUT_SERVER, 'SCRIPT_URI');
            if ($SCRIPT_URI === null){
                $SCRIPT_URI = getenv('SCRIPT_URI');
            }
            if ($absolute_root == substr($SCRIPT_URI,0,$l)){
                return;
            }
            
            $https = filter_input(INPUT_SERVER, 'HTTPS');
            if(!empty($https) && ('off' !== $https)){
                return;
            }
            $HTTP_X_FORWARDED_PROTO = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_PROTO');
            if($HTTP_X_FORWARDED_PROTO === 'https'){
                return;
            }
            $REQUEST_SCHEME = filter_input(INPUT_SERVER, 'REQUEST_SCHEME');
            if($REQUEST_SCHEME === 'https'){
                return;
            }
            
            if ( ($https === null) &&($HTTP_X_FORWARDED_PROTO === null) &&($REQUEST_SCHEME === null)){
                $https = getenv('HTTPS');
                if(!empty($https) && ('off' !== $https)){
                    return;
                }
                $HTTP_X_FORWARDED_PROTO = getenv('HTTP_X_FORWARDED_PROTO');
                if($HTTP_X_FORWARDED_PROTO === 'https'){
                    return;
                }
                $REQUEST_SCHEME = getenv('REQUEST_SCHEME');
                if($REQUEST_SCHEME === 'https'){
                    return;
                }
            }
            if ($SCRIPT_URI){
                $CURRENT_URI = $SCRIPT_URI;
            }else {
                $CURRENT_URI = filter_input(INPUT_SERVER, 'HTTP_HOST');
                if ($CURRENT_URI === null){
                    $CURRENT_URI = getenv('HTTP_HOST').getenv('REQUEST_URI');
                } else {
                    $CURRENT_URI .= filter_input(INPUT_SERVER, 'REQUEST_URI');
                }
            }
            $l = strlen($absolute_root)-8;
            //error_loG(sprintf("-- A == B: |%s| == |%s|", substr($absolute_root,8),substr($CURRENT_URI,0,$l)));
            
            if (substr($absolute_root,8) == substr($CURRENT_URI,0,$l)){
                
                $absolute_url = $absolute_root .  substr($CURRENT_URI,$l);
                //error_loG("====ABS URL IS: $absolute_url|");
                //error_loG("==== MADE FROM C: $CURRENT_URI|");
            } else {
                $path = parse_url($absolute_root,PHP_URL_PATH);
                $REQUEST_URI = getenv('REQUEST_URI');
                $absolute_url = $absolute_root. $REQUEST_URI;
                if ($path){
                    $l = strlen($path);
                    if ($path == substr($REQUEST_URI,0,$l)){
                        $absolute_url = $absolute_root. substr($REQUEST_URI,$l);
                        //error_log("X abs: |$absolute_url|");
                    }
                }
                //error_loG("====ABS URL IS: $absolute_url|");
                //error_loG("==== MADE FROM R: $REQUEST_URI|");
                //error_loG("==== MADE FROM P: $path|");
            }
            
            //error_log("REDIRECTING TO SECURE HOST: |$absolute_url|");
            header("Location: $absolute_url",true,307);
            die();
        }
    }
    
}
