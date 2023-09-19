<?php
namespace Compago\Http;

use \Compago\Http\Message\UriTrait;

class Uri implements UriInterface{
    use UriTrait;
    
    
    /**
     * Create new Uri.
     *
     * @param string $scheme   Uri scheme.
     * @param string $host     Uri host.
     * @param int    $port     Uri port number.
     * @param string $path     Uri path.
     * @param string $query    Uri query string.
     * @param string $fragment Uri fragment.
     * @param string $user     Uri user.
     * @param string $password Uri password.
     */
    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = '',
        $password = ''
    ) {
        $this->scheme = $this->filterScheme($scheme);
        $this->setHost($host);
        if ($port){
            $this->port = $this->filterPort($port);
        }
        $this->path = empty($path) ? '/' : $this->filterPath($path);
        $this->query = $this->filterQuery($query);
        $this->fragment = $this->filterQuery($fragment);
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Create new Uri from string.
     *
     * @param  string $uri Complete Uri string
     *     (i.e., https://user:pass@host:443/path?query).
     *
     * @return self
     */
    public static function createFromString($uri)
    {
        if (!is_string($uri) && !method_exists($uri, '__toString')) {
            throw new \InvalidArgumentException('Uri must be a string');
        }

        $parts = parse_url($uri);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? $parts['pass'] : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? $parts['port'] : null;
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? $parts['fragment'] : '';

        return new static($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }

    /**
     * Create new Uri from string.
     *
     * @return self
     */
    public static function createFromHost()
    {
        $https =  isset($_SERVER['HTTPS'])? strtolower($_SERVER['HTTPS']):'';
        $isSecure = !(empty($https) || $https === 'off');
        $scheme = $isSecure ? 'https' : 'http';
		if(!empty($_SERVER["SERVER_NAME"]) && ($_SERVER["SERVER_NAME"] != '0.0.0.0')){
            $host = $_SERVER["SERVER_NAME"];
		} elseif(!empty($_SERVER["HTTP_HOST"])){
            $host = $_SERVER["HTTP_HOST"];
		} else {
            $host = '';
        }
        
        $new = new static($scheme,$host);
        
        if(!empty($_SERVER["SERVER_PORT"])){
            $port = $new->filterPort($_SERVER["SERVER_PORT"]);
            $new->port = $port;
		} elseif ($isSecure){
            $port = 443;
		} else {
            $port = 80;
		}
		if (($new->scheme == 'http' && $port == 80) ||
            ($new->scheme == 'https' && $port == 443)) {
            $new->port = null;
        }
        
        
        return $new;
    }
	public static function createFromRequest()
	{
		$uri = '';
        $virtualPath = '';
        $basePath = '';
        if (isset($_SERVER['REQUEST_URI'])){
			$uri = $_SERVER['REQUEST_URI'];
		} elseif (isset($_SERVER['SCRIPT_URI'])){
			$uri = $_SERVER['SCRIPT_URI'];
		}
        $SCRIPT_NAME = isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:'';
        if (!$SCRIPT_NAME && isset($_SERVER['SCRIPT_FILENAME'])){
            $SCRIPT_NAME = '/' . implode('/', array_filter(array_slice(explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']), -1)));
        }
        
        if(isset($_SERVER["PATH_INFO"])){
            $virtualPath = $_SERVER["PATH_INFO"];
            $basePath = $SCRIPT_NAME;
        }elseif($uri){
            if (substr($uri, strlen($SCRIPT_NAME)) == $SCRIPT_NAME){
                $virtualPath = substr($uri, strlen($SCRIPT_NAME));
                if ($virtualPath){
                    $basePath = $SCRIPT_NAME;
                }
            }
            if (strstr($virtualPath, '?')){
                $virtualPath = substr($virtualPath, 0, strpos($virtualPath, '?'));
            }
        }
        
        $new = self::createFromString($uri);
        $https =  isset($_SERVER['HTTPS'])? strtolower($_SERVER['HTTPS']):'';
        $isSecure = !(empty($https) || $https === 'off');
        $new->scheme = $isSecure ? 'https' : 'http';
		if(!empty($_SERVER["SERVER_PORT"])){
            $port = $new->filterPort($_SERVER["SERVER_PORT"]);
            $new->port = $port;
		} elseif ($isSecure){
            $port = 443;
		} else {
            $port = 80;
		}
		if (($new->scheme == 'http' && $port == 80) ||
            ($new->scheme == 'https' && $port == 443)) {
            $new->port = null;
        }
		if(!empty($_SERVER["SERVER_NAME"]) && ($_SERVER["SERVER_NAME"] != '0.0.0.0')){
            $new->host = $_SERVER["SERVER_NAME"];
		} elseif(!empty($_SERVER["HTTP_HOST"])){
            $new->setHost($_SERVER["HTTP_HOST"]);
		}
        if ($virtualPath){
            if (substr($virtualPath,0,1)!=='/'){
                $virtualPath = '/' . $virtualPath;
            }
            $new = $new->withPath($virtualPath);
        }
        
        if ($basePath) {
            $new = $new->withBasePath($basePath);
        }
        return $new;
	}
    /**
     * Create new Uri from environment.
     *
     * @param Environment $env
     *
     * @return self
     */
    public static function createFromEnvironment(Environment $env)
    {
        // Scheme
        $https = $env->get('HTTPS');
        $isSecure = !(empty($https) || $https === 'off');
        $scheme =  $isSecure ? 'https' : 'http';

        // Authority: Username and password
        $username = $env->get('PHP_AUTH_USER', '');
        $password = $env->get('PHP_AUTH_PW', '');

        // Authority: Host
        if ($env->has('HTTP_HOST')) {
            $host = $env->get('HTTP_HOST');
        } else {
            $host = $env->get('SERVER_NAME');
        }

        // Authority: Port
        $port = (int)$env->get('SERVER_PORT', 80);
        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $host, $matches)) {
            $host = $matches[1];

            if (isset($matches[2])) {
                $port = (int) substr($matches[2], 1);
            }
        } else {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                $port = (int) substr($host, $pos + 1);
                $host = strstr($host, ':', true);
            }
        }

        // Path
        $requestScriptName = parse_url($env->get('SCRIPT_NAME'), PHP_URL_PATH);
        $requestScriptDir = dirname($requestScriptName);

        // parse_url() requires a full URL. As we don't extract the domain name or scheme,
        // we use a stand-in.
        $requestUri = parse_url('http://example.com' . $env->get('REQUEST_URI'), PHP_URL_PATH);

        $basePath = '';
        $virtualPath = $requestUri;
        if (stripos($requestUri, $requestScriptName) === 0) {
            $basePath = $requestScriptName;
        } elseif ($requestScriptDir !== '/' && stripos($requestUri, $requestScriptDir) === 0) {
            $basePath = $requestScriptDir;
        }

        if ($basePath) {
            $virtualPath = ltrim(substr($requestUri, strlen($basePath)), '/');
        }

        // Query string
        $queryString = $env->get('QUERY_STRING', '');
        if ($queryString === '') {
            $queryString = parse_url('http://example.com' . $env->get('REQUEST_URI'), PHP_URL_QUERY);
        }

        // Fragment
        $fragment = '';

        // Build Uri
        $uri = new static($scheme, $host, $port, $virtualPath, $queryString, $fragment, $username, $password);
        if ($basePath) {
            $uri = $uri->withBasePath($basePath);
        }

        return $uri;
    }
	public static function createFromReferrer()
	{
		if (isset($_SERVER['HTTP_REFERER'])){
			return self::createFromString($_SERVER['HTTP_REFERER']);
		}
		return self::createFromString('');
	}
    
    private function setHost($host){
        if (strpos($host,':') !== false){
            $x = explode(':',$host);
            $this->host = $x[0];
            $this->port = (int)$x[1];
        } else {
            $this->host = $host;
        }
    }

    public function hasBasePath()
    {
        return !!$this->basePath;
    }
    
    /**
     * 
     *
     * @return bool
     */
    public function isHttps()
    {
        return strtolower($this->scheme) === 'https';
    }
}