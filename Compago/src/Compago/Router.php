<?php

namespace Compago;
/*
$ROUTER = new Compago\Router;

$ROUTER->get('/','IndCont');
$ROUTER->get('/index','IndCont');
$ROUTER->get('/member/:id','ProfCont@onShow');
$ROUTER->get('/member/:id/edit','ProfCont@onShow');
$ROUTER->get('/member/:id/message/:msgid','ProfCont');
$ROUTER->get('/profile','ProfCont');
$ROUTER->get('/profile/:id-:id2','ProfCont@onShow');
$ROUTER->get('/profile/:id/:id2','ProfCont@onShow');
$ROUTER->get('/fx/:id',function($R,$RES,$args){
     echo "called: ". __FUNCTION__;
        print_r($args);
        extract($args);
        print_r(get_defined_vars());
});
$ROUTER->getMatchedRoute($method=null, $path=null);
$ROUTER->group([attributes], function($subRouter){
    $subRouter->get('/','IndCont');
    $subRouter->get('/index','IndCont');

})
$router->group('/apple',function($router){
    $router->addParameterRegEx('appleid','[\d]+');
    $router->get('/tree','apple-tree');
    //$router->get('/:id','apple-id');
    $router->get('/:appleid','apple-appleid');
    $router->get('/leaf','apple-leaf');
});


GROUP ATTRIBUTES
- prefix
- pattern
===NOTES
$ROUTER->get('/','IndCont') === $ROUTER->get('','IndCont');
$ROUTER->get('/n','IndCont') !== $ROUTER->get('n','IndCont');
$ROUTER->get('*','IndCont'); is a catch all address
$ROUTER->get('/n/*','IndCont'); is NOT a catch all address
$ROUTER->group('/z',function($router){
    $router->get('/','IndCont') === $router->get('','IndCont');
    $router->get('/n','IndCont') !== $router->get('n','IndCont');
    $router->get('/n','IndCont') === $ROUTER->get('/z/n','IndCont');
    $router->get('n','IndCont') === $ROUTER->get('/zn','IndCont');
    $router->get('*','IndCont'); is a catch all address
    $router->get('*','IndCont') === $ROUTER->get('/z'  +{CATCH ALL},'IndCont');
    
});
*/


function segment_len($pattern)
{
    /*
    /blog/to/:year/:month  == 4 segments
    /blog/to/:year/:month/ == 4 segments
     blog/to/:year/:month/ == 4 segments
     blog/to/:year/:month == 4 segments
    */
    if (strlen($pattern) == 0) {
        return 0;
    }
    if ('*' == $pattern) {
        return 0;
    }
    if ('/' == $pattern) {
        return 1;
    }
    $len = substr_count(trim($pattern, '/'), '/');
    return $len + 1;
}
function pattern_len($pattern)
{

    if ('*' == $pattern) {
        return 0;
    }
    if ('/' == $pattern) {
        return 1;
    }
    $tokens = pattern_tokens($pattern, array());
    $len = 0;
    foreach ($tokens as $token) {
        $len += strlen($token['text']);
        if (isset($token['name'])) {
            $len +=  1;
        }
    }
    return $len;
}

function pattern_tokens($pattern, $options)
{
    /**
     *  the path like:
     *
     *      /blog/to/:year/:month
     *
     *  will be separated like:
     *      
     *      [
     *          '/blog/to',  (text token)
     *          '/:year',    (reg exp token)
     *          '/:month',   (reg exp token)
     *      ]
     */
    /*preg_match_all('/(?:
            # parse variable token with separator
            .            # separator
            :([\w\d_]+)  # variable
            |
            # optional tokens
            \((.*)\)
        )/x', $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);*/
    preg_match_all('/(?:.:([\w\d_]+))/x', $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
    //print_r($matches);

    $tokens = array();
    $variables = array();
    $pos = 0;
    $seps = array($pattern[$pos]);
    $len = strlen($pattern);
    foreach ($matches as $matchIdx => $match) {
        // the first char from pattern (which is the seperater)
        // and the first char from the next match pattern
        // collect the separator tokens from the next matched pattern
        if (isset($matches[$matchIdx + 1])) {
            $nextMatch = $matches[$matchIdx + 1];
            if ($nextMatch[0][0][0] === ':') { // variable token
            } else {
                $seps[] = $nextMatch[0][0][0];
            }
        }
        if (isset($match[0][1])) {
            if ($text = substr($pattern, $pos, $match[0][1] - $pos)) {
                $token = array();
                $token['type'] = 'TEXT';
                $token['text'] = $text;
                $tokens[] = $token;
            }
        }
        if (isset($match[1][0])) {
            $varName = $match[1][0];

            if (in_array($varName, $variables)) {
                throw new Exception("Route pattern compilation failed: two named sections have the same name {$varName} does not exist.");
            }

            if ($pos !== $len) {
                $seps[] = $pattern[$pos];
            }

            $regexp = '';
            if (isset($options[$varName])) {
                $opts  = $options[$varName];
                if (isset($opts['pattern'])) {
                    $regexp = $opts['pattern'];
                }
            }
            if (!$regexp) {
                if (isset($options['_regex_'])) {
                    if (isset($options['_regex_'][$varName])) {
                        $regexp = $options['_regex_'][$varName];
                    }
                }
            }
            if (!$regexp) {
                // use the default pattern (which is based on the separater charactors we got)
                $regexp = sprintf('[^%s]+', preg_quote(implode('', array_unique($seps)), '#'));
            }

            $token = array();
            $token['type'] = 'VARIABLE';
            $token['text'] = $match[0][0][0];
            $token['regex'] = $regexp;
            $token['name'] = $varName;
            $tokens[] = $token;
            $variables[] = $varName;
        }

        $pos = $match[0][1] + strlen($match[0][0]);
    }
    if ($pos < $len) {
        $token = array();
        $token['type'] = 'TEXT';
        $token['text'] = substr($pattern, $pos);
        $tokens[] = $token;
    }
    return $tokens;
}
function compile_pattern($pattern, $options)
{
    $tokens = pattern_tokens($pattern, $options);

    $match_start = isset($options['match-start']) ? !!$options['match-start'] : true;
    $match_end = isset($options['match-end']) ? !!$options['match-end'] : false;
    //print_r($tokens);
    $regexp = '';
    foreach ($tokens as $token) {
        if (isset($token['name'])) {
            $regexp .= sprintf("%s(?P<%s>%s)", preg_quote($token['text'], '#'), $token['name'], $token['regex']);
        } else {
            $regexp .= preg_quote($token['text'], '#');
        }
    }
    if ($regexp == $pattern) {
        return '';
    } else {
        if ($match_start) {
            $regexp = '^' . $regexp;
        }
        if ($match_end) {
            $regexp .= '$';
        }
        return $regexp;
    }
}
class RouterSorting
{
    /**
     * 1. Check groups
     * 2. Put '*' at top 
     * 3. Check all fixed items
     * 4. check all variable patterns
     * 
     *  if a is group then a
     *  if b is group then b
     *  if a is b then escape
     *  if a is * then a
     *  if b is * then b
     *  if a is not VARIABLE then a
     *  if b is not VARIABLE then b
     *  if a has more segment than b then a
     * 
     */
    function __invoke($a, $b)
    {
        if ($a && $b) {
            if ($a->isGroup() && !$b->isGroup()) {
                return -1;
            }
            if (!$a->isGroup() && $b->isGroup()) {
                return 1;
            }
            if ($a->pattern == $b->pattern) {
                return 0;
            }
            if ($a->pattern == '*') {
                return -1;
            }
            if ($b->pattern == '*') {
                return 1;
            }

            $a_s_pos = strpos($a->pattern, ':');
            $b_s_pos = strpos($b->pattern, ':');

            if (($a_s_pos === false) && $b_s_pos) {
                return -1;
            }
            if (($b_s_pos === false) && $a_s_pos) {
                return 1;
            }


            $a_s_count = segment_len($a->pattern);
            $b_s_count = segment_len($b->pattern);
            if ($a_s_count > $b_s_count) {
                return -1;
            } elseif ($a_s_count < $b_s_count) {
                return 1;
            }

            if ($a_s_pos > $b_s_pos) {
                return -1;
            }
            if ($b_s_pos > $a_s_pos) {
                return 1;
            }

            $a_v_count = substr_count($a->pattern, ':');
            $b_v_count = substr_count($b->pattern, ':');
            if ($a_v_count > $b_v_count) {
                return -1;
            } elseif ($a_v_count < $b_v_count) {
                return 1;
            }

            $a_len = pattern_len($a->pattern);
            $b_len = pattern_len($b->pattern);
            if ($a_len == $b_len) {
                return 0;
            } elseif ($a_len > $b_len) {
                return -1;
            } else {
                return 1;
            }
        } elseif ($a->pattern) {
            return -1;
        } elseif ($b->pattern) {
            return 1;
        }
    }
}

class Router_Item
{
    public $pattern;
    protected $is_wildcard =false;
    protected $controller;
    protected $method;
    protected $options = [];
    protected $matched_data = [];

    public function __construct($method, $pattern, $controller, $options = [])
    {
        $this->controller = $controller;
        $this->options = $options;
        $this->method = strtoupper($method);
        $this->setPattern($pattern);
    }
    public function __toString()
    {
        $r = ["Router ({$this->method} {$this->pattern})"];
        $r[] = print_r($this->options, 1);
        return implode('', $r);
    }
    public function isGroup()
    {
        return false;
    }
    public function isWildcard()
    {
        return $this->is_wildcard;
    }
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        $this->is_wildcard = substr($pattern,-1) == '*';
    }
    public function getPattern()
    {
        return $this->pattern;
    }
    public function getCallback()
    {
        if (
            is_string($this->controller)
            && (strpos($this->controller, '@') !== false)
            && (strpos($this->controller, ' ') === false)
        ) {
            $callback = explode('@', $this->controller);
            if (empty($callback[1])) {
                $callback[1] = null;
            }
            return $callback;
        }
        return $this->controller;
    }
    public function getMatchedDataBag()
    {
        return new \Compago\Tools\PropertyBag($this->matched_data);
    }
    public function getMatchedData()
    {
        return $this->matched_data;
    }
    public function setMatchedData($data)
    {
        $this->matched_data = $data;
    }
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
    public function getOptions()
    {
        return $this->options;
    }
    public function getOption($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }
    public function getDefaultData()
    {
        $key = 'default-data';
        if (isset($this->options[$key]) && is_array($this->options[$key])) {
            return $this->options[$key];
        }
        return [];
    }
}

class RouterGroup extends Router_Item
{
    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'/*,'TRACE','CONNECT'*/];
    protected $callback;
    protected $attributes = array();
    protected $routes = array();
    protected $local_regex_patterns = array();
    protected static $global_regex_patterns = array(
        'id' => '[\d]+',
        'name' => '[-A-Za-z0-9]+',
        'code' => '[-A-Za-z0-9]+',
        'title' => '[-A-Za-z0-9 ]+',
        'username' => '[_.-A-Za-z0-9]+',
        'filename' => '[_.-A-Za-z0-9]+',
        'slug' => '[-A-Za-z0-9_]+',
        'path' => '[.-A-Za-z0-9\\/_]+',
        'version' => '[v]*[\d.]+',
    );
    public static function lookupParameterRegEx($varName)
    {
        if (isset(self::$global_regex_patterns[$varName])) {
            return self::$global_regex_patterns[$varName];
        }
        return '';
    }
    public static function setParameterRegEx($varName, $pattern)
    {
        self::$global_regex_patterns[$varName] = $pattern;
    }
    public function addParameterRegEx($varName, $pattern)
    {
        $this->local_regex_patterns[$varName] = $pattern;
    }
    public function __construct($attributes = array(), $callback = null, $options = array())
    {
        if ($callback) {
            $this->callback = \Closure::fromCallable($callback);
        }
        $this->options = $options;
        $this->setAttributes($attributes);
        if (count($attributes)) {
            $this->method = 'ANY';
        }
    }
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        if (isset($this->attributes['prefix'])) {
            $this->setPattern($this->attributes['prefix']);
        }
    }
    public function setPrefix($prefix)
    {
        $this->attributes['prefix'] = $prefix;
        $this->setPattern($prefix);
    }
    public function isGroup()
    {
        return count($this->attributes);
    }
    public function group($attributes, $callback)
    {
        if (!is_array($attributes)) {
            $attributes = ['prefix' => func_get_arg(0)];
        }
        $r = new RouterGroup($attributes, $callback);
        $this->routes[] = $r;
        return $r;
    }

    public function __call($name, $arguments)
    {
        $name = strtoupper($name);
        if (in_array($name, self::$verbs) || $name == 'ANY') {
            array_unshift($arguments, $name);
            return call_user_func_array([$this, 'createRoute'], $arguments);
        }
    }
    public function get($pattern, $controller, $options = array())
    {
        $this->createRoute('HEAD', $pattern, $controller, $options);
        return $this->createRoute('GET', $pattern, $controller, $options);
    }
    public function route($method, $pattern, $controller, $options = array())
    {
        return $this->createRoute($method, $pattern, $controller, $options);
    }
    private function createRoute($method, $pattern, $controller, $options = array())
    {
        if (is_array($method)) {
            $r = array();
            foreach ($method as $fx) {
                $r[] = $this->createRoute($fx, $pattern, $controller, $options);
            }
            return $r;
        }


        $options = array_merge($this->options, $options);
        if (is_array($pattern)) {
            $r = array();
            foreach ($pattern as $pat) {
                if (isset($this->attributes['prefix']) && ($pat != '*')) {
                    $pat = $this->attributes['prefix'] . $pat;
                }
                $a = new Router_Item($method, $pat, $controller, $options);
                $this->routes[] = $a;
                $r[] = $a;
            }
        } else {
            if (isset($this->attributes['prefix']) && ($pattern != '*')) {
                $pattern = $this->attributes['prefix'] . $pattern;
            }
            $r = new Router_Item($method, $pattern, $controller, $options);
            $this->routes[] = $r;
            return $r;
        }
        return $r;
    }
    public function getMatchedRoute($method, $path)
    {
        usort($this->routes, new RouterSorting());

        $route = false;
        $found = false;
        $matches = array();
        $hasCatchAllRoute = false;

        foreach ($this->routes as $r) {
            if ($r->method == $method || ($r->method == 'ANY')) {
                if ($r->isGroup() && ($r->pattern == substr($path, 0, strlen($r->pattern)))) {
                    $found = true;
                    $route = $r;
                    break;
                } elseif ($r->pattern) {
                    $route_pattern = $r->pattern == '/'? $r->pattern: rtrim($r->pattern, '/');

                    if ($route_pattern == $path) {
                        $found = true;
                        $route = $r;
                        break;
                    } elseif ($r->pattern == '*') {
                        $hasCatchAllRoute = $r;
                    } else {
                        $options = array_merge($this->options, $r->options);
                        $options['_regex_'] = array_merge(self::$global_regex_patterns, $this->local_regex_patterns);
                        $compiled_pattern = compile_pattern($r->pattern, $options);
                        // __er("$path| == |$compiled_pattern=>$r->pattern|");
                        if ($compiled_pattern && preg_match('#' . $compiled_pattern . '#', $path, $matches)) {
                            //    __er("MATCHED");
                            $found = true;
                            $route = $r;
                            break;
                        }
                    }
                } else {
                }
            }
        }
        if (empty($matches)) {
            $matches = array($path);
        }

        if ($found && $route) {
            $default_data = array_merge($this->getDefaultData(), $route->getDefaultData());
            foreach ($default_data as $key => $name) {
                if (!isset($matches[$key])) {
                    $matches[$key] = $name;
                }
            }
            $route->setMatchedData($matches);
            $route = $this->returnDownPipeline($route, $method, $path);
            if ($route) {
                return $route;
            }
        }
        if ($hasCatchAllRoute) {
            $default_data = array_merge($this->getDefaultData(), $hasCatchAllRoute->getDefaultData());
            foreach ($default_data as $key => $name) {
                if (!isset($matches[$key])) {
                    $matches[$key] = $name;
                }
            }
            $hasCatchAllRoute->setMatchedData($matches);
            $hasCatchAllRoute = $this->returnDownPipeline($hasCatchAllRoute, $method, $path);
            return $hasCatchAllRoute;
        }
        return false;
    }
    private function returnDownPipeline($route, $method, $path)
    {
        if ($route->isGroup()) {
            $matched_data = $route->getMatchedData();
            if (is_callable($route->callback)) {
                $route->callback->call($route, $route, $method, $path);
            }
            $r = $route->getMatchedRoute($method, $path);
            if ($r) {
                $route = $r;
                $matched_data2 = $route->getMatchedData();
                foreach ($matched_data as $key => $name) {
                    if (!isset($matched_data2[$key])) {
                        $matched_data2[$key] = $name;
                    }
                }
            }
            $route->setMatchedData($matched_data2);
        }
        return $route;
    }
}
class Router extends RouterGroup
{
}
