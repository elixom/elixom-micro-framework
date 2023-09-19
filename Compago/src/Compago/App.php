<?php

namespace Compago;

use \Compago\Router;
use \Compago\Http\Response;
use \Compago\Http\ResponseInterface;

include_once('expose-helpers.php');

class App
{
    protected $app_root = '';
    protected $path_prefix = '';
    protected $request;
    /**
     * @var callable
     */
    protected $exceptionHandler;
    public $auto_route = true;
    public $https = false;
    protected $injected = array();
    protected $middleware = array();
    protected $routeMiddleware = array();
    protected $properties = array();
    public function __construct($app_root = '.')
    {

        $app_root = rtrim(realpath($app_root), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->app_root = $app_root;

        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $SCRIPT_NAME = $_SERVER['SCRIPT_NAME'];
        } else {
            $SCRIPT_NAME = substr($_SERVER['SCRIPT_FILENAME'], strlen($DOCUMENT_ROOT));
        }
        $x = explode(DIRECTORY_SEPARATOR, $SCRIPT_NAME);
        array_pop($x);
        $this->path_prefix = '/' . implode('/', array_filter($x));

        spl_autoload_register(function ($className) use ($app_root) {
            $className = ltrim($className, '\\');
            $path = $app_root;
            //error_log("oC= $className");
            if (substr($className, -10) == 'Controller') {
                $path .= 'Controllers';
                $type = 'Controllers';
            } elseif (substr($className, -4) == 'View') {
                $path .= 'Views';
                $type = 'Views';
            } elseif (substr($className, -5) == 'Model') {
                $path .= 'Models';
                $type = 'Models';
            } elseif (substr($className, 0, 11) == 'App\\Models\\') {
                $path .= 'Models';
                $type = 'Models';
            } elseif (substr($className, -10) == 'Middleware') {
                $path .= 'Middleware';
                $type = 'Middleware';
            } elseif (substr($className, 0, 11) == 'App\\Schema\\') {
                $path .= 'Schema';
                $type = 'Schema';
            } else {
                return false;
            }

            $prefix = "App\\{$type}\\";
            if (substr($className, 0, strlen($prefix)) == $prefix) {
                $className = substr($className, strlen($prefix));
            } else {
                return false;
            }

            $fileName = '';
            if ($lastNsPos = strripos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName = $path . DIRECTORY_SEPARATOR . $fileName . $className . '.php';

            //error_log("C= $fileName");
            if (file_exists($fileName)) {
                require $fileName;
                return true;
            }
            $arr = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/x', $className);
            $type = array_pop($arr);
            $fileName = $path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $arr) . $type . '.php';
            //error_log("CC= $fileName");
            if (file_exists($fileName)) {
                require $fileName;
                return true;
            }
            $fileName = $path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $arr) . '.php';
            //error_log("CC= $fileName");
            if (file_exists($fileName)) {
                require $fileName;
                return true;
            }
            return false;
        });
    }
    public function inject($key, $callback)
    {
        $this->injected[$key] = $callback;
    }
    public function middleware($callback)
    {
        $this->middleware[] = $callback;
    }
    public function setExceptionHandler($callback)
    {
        $this->exceptionHandler = $callback;
    }
    public function __get($name)
    {
        if ($name == 'router') {
            return router();
        }
        if (array_key_exists($name, $this->injected)) {
            return $this->injected[$name];
        }
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }
    public static function startSession()
    {
        new \Compago\Session;
    }
    public static function router()
    {
        return router();
    }
    public static function isDevelopment(){
        if (isset($_ENV['DEV']) && ($_ENV['DEV'])) {
            return true;
        }
        return false;
    }
    public static function isLocalHost()
    {
        if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] == 'localhost')) {
            return true;
        }
        if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == '127.0.0.1')) {
            return true;
        }
        return false;
    }
    public function view($name, $withData = array())
    {
        $path = $this->app_root . 'templates' . DIRECTORY_SEPARATOR;
        $knownExt = substr($name, -4);
        $textView = false;
        if (substr($name, -3) === '.js') {
            $fileName = $path . $name;
            if (!file_exists($fileName)) {
                $fileName = $path . str_replace('.', DIRECTORY_SEPARATOR, substr($name, 0, -3)) . '.js';
            }
            $textView = true;
        } else if (substr($name, -4) === '.css') {
            $fileName = $path . $name;
            if (!file_exists($fileName)) {
                $fileName = $path . str_replace('.', DIRECTORY_SEPARATOR, substr($name, 0, -4)) . '.css';
            }
            $textView = true;
        } else {
            $fileName = $path . $name . '.php';
            if (!file_exists($fileName)) {
                $fileName = $path . $name . '.html';
                if (!file_exists($fileName)) {
                    $fileName = $path . str_replace('.', DIRECTORY_SEPARATOR, $name) . '.php';
                    if (!file_exists($fileName)) {
                        $fileName = $path . str_replace('.', DIRECTORY_SEPARATOR, $name) . '.html';
                    }
                }
            }
        }
        if (file_exists($fileName)) {
            //error_log("V exists: $fileName");
            if ($textView) {
                $view = new \Compago\View\ViewText($this, $name, $fileName, $withData);
            } else {
                $view = new \Compago\View\View($this, $name, $fileName, $withData);
            }
        } else {
            //error_log("V NO exists: $fileName");
            $view = new \Compago\View\ExceptionView('View does not exist: ' . $name);
        }


        return $view;
    }

    public function getRequest()
    {
        return $this->request;
    }
    public function run()
    {
        try {
            $scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
            $REQUEST = \Compago\Http\ServerRequest::createFromGlobals();
            $this->request = $REQUEST;

            $RESPONSE  = new Response;


            $midWares = array_values($this->middleware);
            if (count($midWares)) {
                $i = 0;
                $anonFx = function ($REQUEST) use (&$i, &$anonFx, &$RESPONSE, &$midWares) {
                    $i++;
                    if (isset($midWares[$i])) {
                        return $midWares[$i]($REQUEST, $anonFx);
                    }
                    return $RESPONSE;
                };
                $RESPONSE = $midWares[$i]($REQUEST, $anonFx);
            }

            if ($RESPONSE === null) {
                $RESPONSE  = new Response;
            }

            $URL = $REQUEST->getUri();
            $request_method = $REQUEST->getMethod();
            $plain_path = trim($URL->getPath(), '/');
            $path_prefix = trim($this->path_prefix, '/');
            //__Er('p-p', $plain_path, $path_prefix);
            //REMOVE path prefix
            $ppl = strlen($path_prefix);
            if ($ppl) {
                if (substr($plain_path, 0, $ppl) == $path_prefix) {
                    $plain_path = trim(substr($plain_path, $ppl), '/');
                }
            }
            $route_path = '/' . $plain_path;
           // __Er('rp', $route_path);
            $newResponse = $RESPONSE;
            //__er('RM',$request_method,'rts');
            $matchedRoute = router()->getMatchedRoute($request_method, $route_path);
            //__er($matchedRoute);

            if ($matchedRoute) {
                //$matches = $matchedRoute->getMatchedData();
                $callback = $matchedRoute->getCallback();
                if (is_array($callback)) {
                    $class = $callback[0];
                    $class_method = $callback[1];
                    if (!class_exists($class)) {
                        $class = 'App\Controllers\\' . $class;
                        if (!class_exists($class)) {
                            throw new Exception("Controller [{$class}] does not exist.");
                        }
                    }
                    // rebless a controller (extract this to common method)
                    $controller = new $class();
                    if ($class_method && method_exists($controller, $class_method)) {
                        $newResponse = $controller->$class_method($REQUEST, $RESPONSE, $matchedRoute);
                    } elseif (is_callable($controller)) {
                        $newResponse = $controller($REQUEST, $RESPONSE, $matchedRoute);
                    } else {
                        throw new Exception("Method [$class_method] not found in controller [$class] and the controller is not callable for {$matchedRoute}.");
                    }
                } elseif (is_callable($callback)) {
                    //add parameter
                    $newResponse = $callback($REQUEST, $RESPONSE, $matchedRoute);
                } elseif (is_string($callback)) {
                    //add parameter
                    $newResponse = $callback;
                } else {
                    throw new Exception("Controller [{$callback}] is not callable for {$matchedRoute}.");
                }
            } elseif ($this->auto_route) {
                $matchedRoute = new \Compago\Router;
                if ($plain_path) {
                    $segments = explode('/', ltrim($plain_path, '/'));
                    $class = 'App\Controllers\\' . ucfirst($segments[0]) . "Controller";

                    if (!class_exists($class)) {
                        $class = "App\Controllers\DefaultController";
                    }
                } else {
                    $class = "App\Controllers\DefaultController";
                }

                if (!class_exists($class)) {
                    throw new Exception("Controller {$class} does not exist.");
                }
                $controller = new $class();
                if (is_callable($controller)) {
                    $newResponse = $controller($REQUEST, $RESPONSE, $matchedRoute);
                } else {
                    throw new Exception("Controller [$class] is not callable and does not have an onIndex method for {$matchedRoute}.");
                }
            } else {
            }
        } catch (\Exception $e) {
            $newResponse = $e;
        }

        if ($newResponse instanceof ResponseInterface) {
            // if route callback returns a ResponseInterface, then use it
            $RESPONSE = $newResponse;
        } elseif ($newResponse instanceof \Compago\View\View) {
            // if route callback returns a View, then use it
            $RESPONSE->setBody($newResponse->render());
        } elseif ($newResponse instanceof \Exception) {
            // if route callback returns a Exception, render Exception
            if (!($RESPONSE instanceof ResponseInterface)) {
                // if route callback returns a ResponseInterface, then use it
                $RESPONSE  = new Response();
            }
            $RESPONSE->setBody($this->renderException($newResponse));
        } elseif (is_string($newResponse)) {
            // if route callback returns a string, then append it to the response
            $RESPONSE->setBody($newResponse);
        } elseif (is_array($newResponse)) {
            // if route callback returns an array, then send it as json
            $RESPONSE->setBody($newResponse);
        } elseif ($newResponse !== null) {
            $RESPONSE->setBody($newResponse);
        }
        $RESPONSE->prepare($REQUEST);
        $RESPONSE->send();
    }
    public function renderException($exception)
    {
        if (is_callable($this->exceptionHandler)){
            $callback = $this->exceptionHandler;
            $ret = $callback($exception,$this);
            if ($ret !== false){
                return $ret ;
            }
        }
        $arr = ['<pre>Compago\App Exception Render: ', get_class($exception) ,"\n"];
        $arr[] = $exception->getMessage();
        if ($this->isLocalHost()){
            //the check should be production vs dev
            $arr[] = sprintf(" in %s(%d)",$exception->getFile(),$exception->getLine());
        }
        $arr[] = "\n";
        if ($this->isDevelopment()){
            //the check should be production vs dev
            $arr[] = $exception->getTraceAsString();
            
        } 
        
        __er($e);
        $arr[] = '</pre>';
        return implode('',$arr);
    }
    public function uri()
    {
        $uri = \Compago\Http\Uri::createFromHost()->withBasePath($this->path_prefix);
        if (func_num_args()) {
            $uri = $uri->withPath(implode('/', func_get_args()));
        }
        return $uri;
    }
    public function path()
    {
        $uri = $this->app_root;
        if (func_num_args()) {
            $uri .= implode(DIRECTORY_SEPARATOR, func_get_args());
        }
        return $uri;
    }
    public function set($key, $value)
    {
        $this->properties[$key] = $value;
    }
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        }
        return $default;
    }
}
