<?php
if (! function_exists('redirect')) {
    function redirect($url,$status=302,$headers=array()){
        return new \Compago\Http\RedirectResponse($url,$status,$headers);
    }
}
if (! function_exists('response')) {
    function response($content = '', $status = 200, array $headers = [])
    {
        return new \Compago\Http\Response($content, $status, $headers);
    }
}
