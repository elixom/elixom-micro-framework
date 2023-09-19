<?php

use function Compago\Http\redirect;
class HTML extends Compago\Html\HtmlUtils{}

function app(){
    static $app = null;
    if ($app === null){
        $app =  new \Compago\App(__DIR__);
        $app->set('title','Compago Project');
        $app->middleware(new App\Middleware\SayMiddleware);
        $app->middleware(new App\Middleware\AfterMiddleware);
        $app->middleware(new Compago\Middleware\RequireHttps);
    }
    return $app;
}
if (! function_exists('env')) {
    function env($key = null, $default = null){
        if (isset($_SERVER[$key])){
            return $_SERVER[$key];
        }
        if (isset($_ENV[$key])){
            return $_ENV[$key];
        }
        return $default;
    } 
}

if (! function_exists('db')) {
    function db($connection_id = null){
        if (!$connection_id){
            $connection_id = 'default';
        }
        static $conns =array();
        if (!isset($conns[$connection_id])){
            $port=$socket=$flags=null;
            GLOBAL $CFG;
            $hostname = $CFG->dbhost;
            $username = $CFG->dbuser;
            $password = $CFG->dbpass;
            $database = $CFG->dbname;
            
            if(isset($CFG->dbport)) $port = $CFG->dbport;
            if(isset($CFG->dbsocket)) $socket = $CFG->dbsocket;
            if(isset($CFG->dbflags)) $flags = (int)$CFG->dbflags;
            
            $conns[$connection_id] = Compago\Database\Database::connect($hostname, $username, $password, $database,$port,$socket,$flags);
            $conns[$connection_id]->configTimezone();
        }
        return $conns[$connection_id];
    } 
    class DB{
        public static function __callStatic($name, $arguments) {
            $link = db();
            if(method_exists($link,$name)){
                return call_user_func_array(array($link,$name),$arguments);
            }
        }
    }
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Session\Store|\Illuminate\Session\SessionManager
     */
    /*function session($key = null, $default = null)
    {
        /*if (is_null($key)) {
            return app('session');
        }

        if (is_array($key)) {
            return app('session')->put($key);
        }

        return app('session')->get($key, $default);* /
    }*/
    if (! function_exists('session')) {
        function session(){
            static $session = null;
            if ($session === null){
                $session =  new Compago\Session;
            }
            return $session;
        }
    }
}

/**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function asset($path, $secure = null)
    {
        //return app('url')->asset($path, $secure);
    }

require_once 'routes.php';

//require_once 'test-routes.php';
