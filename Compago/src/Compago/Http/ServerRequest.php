<?php
namespace Compago\Http;

use \Compago\Contracts\Arrayable;


class ServerRequest extends Request{
    
    /**
     * The original request method (ignoring override)
     *
     * @var string
     */
    protected $originalMethod;
    /**
     * The request query string params
     *
     * @var array
     */
    protected $queryParams;

    /**
     * The request cookies
     *
     * @var array
     */
    protected $cookies;

    /**
     * The server environment variables at the time the request was created.
     *
     * @var array
     */
    
    protected $serverParams;
    /**
     * The request body parsed (if possible) into a PHP array or object
     *
     * @var null|array|object
     */
    protected $bodyParsed = false;

    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return static
     */
    public static function createFromGlobals()
    {
        $method = 'GET';
        if(isset($_SERVER["REQUEST_METHOD"])){
            $method = $_SERVER["REQUEST_METHOD"];
        }
        $url = \Compago\Http\Uri::createFromRequest();
        
        $request = new static($method,$url);
        
        if ($method == 'POST'){
            $request->body = new ParameterBag($_POST);
            //$request->files = new FileBag($files);
        }
        
        $request->request = new ParameterBag($_REQUEST);
        $request->query = new ParameterBag($_GET);
        $request->attributes = new ParameterBag(array());
        $request->cookies = new ParameterBag($_COOKIE);
        
        $request->server = new ServerBag($_SERVER);
        $request->headers = new HeaderBag($request->server->getHeaders());

/*
        $request->cookies = $_COOKIE;
        $request->serverParams = $_SERVER;
        $request->attributes = ;
        $request->bodyParsed = $_POST;
        $request->uploadedFiles = UploadedFile::createFromGlobal();
        $request->queryParams = $_GET;*/
        
        
         
/*
        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && \in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }*/

        return $request;
    }

    /**
     * Clones the current request.
     *
     * Note that the session is not cloned as duplicated requests
     * are most of the time sub-requests of the main one.
     */
    public function __clone()
    {
        $this->query = clone $this->query;
        $this->request = clone $this->request;
        $this->attributes = clone $this->attributes;
        $this->cookies = clone $this->cookies;
        $this->files = clone $this->files;
        $this->server = clone $this->server;
        $this->headers = clone $this->headers;
    }
     /**
     * Returns the request as a string.
     *
     * @return string The request
     */
    public function __toString()
    {
        try {
            $content = $this->getContent();
        } catch (\LogicException $e) {
            return trigger_error($e, E_USER_ERROR);
        }

        $cookieHeader = '';
        $cookies = array();

        foreach ($this->cookies as $k => $v) {
            $cookies[] = $k.'='.$v;
        }

        if (!empty($cookies)) {
            $cookieHeader = 'Cookie: '.implode('; ', $cookies)."\r\n";
        }

        return
            sprintf('%s %s %s', $this->getMethod(), $this->getRequestUri(), $this->server->get('SERVER_PROTOCOL'))."\r\n".
            $this->headers.
            $cookieHeader."\r\n".
            $content;
    }
    
}
/**
 * Request
 *
 * This class represents an HTTP request. It manages
 * the request method, URI, headers, cookies, and body
 * according to the PSR-7 standard.
 *
 * @link https://github.com/php-fig/http-message/blob/master/src/MessageInterface.php
 * @link https://github.com/php-fig/http-message/blob/master/src/RequestInterface.php
 * @link https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php
 */
abstract class COpiedServerRequest extends Request implements ServerRequestInterface, Arrayable, \ArrayAccess{
    
    
    
    /**
     * The request attributes (route segment names and values)
     *
     * @var \Slim\Collection
     */
    protected $attributes;

    
    /**
     * List of request body parsers (e.g., url-encoded, JSON, XML, multipart)
     *
     * @var callable[]
     */
    protected $bodyParsers = [];

    /**
     * List of uploaded files
     *
     * @var UploadedFileInterface[]
     */
    protected $uploadedFiles;

    /**
     * Valid request methods
     *
     * @var string[]
     * @deprecated
     */
    protected $validMethods = [
        'CONNECT' => 1,
        'DELETE' => 1,
        'GET' => 1,
        'HEAD' => 1,
        'OPTIONS' => 1,
        'PATCH' => 1,
        'POST' => 1,
        'PUT' => 1,
        'TRACE' => 1,
    ];

    /**
     * Create new HTTP request with data extracted from the application
     * Environment object
     *
     * @param  Environment $environment The Slim application Environment
     *
     * @return static
     */
    public static function createFromEnvironment(Environment $environment)
    {
        $method = $environment['REQUEST_METHOD'];
        $uri = Uri::createFromEnvironment($environment);
        $headers = Headers::createFromEnvironment($environment);
        $cookies = Cookies::parseHeader($headers->get('Cookie', []));
        $serverParams = $environment->toArray();
        $body = null;
        //$body = new RequestBody();
        $uploadedFiles = UploadedFile::createFromEnvironment($environment);

        $request = new static($method, $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);

        if ($method === 'POST' &&
            in_array($request->getMediaType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])
        ) {
            // parsed body must be $_POST
            $request = $request->withParsedBody($_POST);
        }
        return $request;
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function initialize(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->request = new ParameterBag($request);
        $this->query = new ParameterBag($query);
        $this->attributes = new ParameterBag($attributes);
        $this->cookies = new ParameterBag($cookies);
        $this->files = new FileBag($files);
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());

        $this->content = $content;
        $this->languages = null;
        $this->charsets = null;
        $this->encodings = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo = null;
        $this->requestUri = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = null;
        $this->format = null;
    }
    
    /**
     * Creates a Request based on a given URI and configuration.
     *
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string               $uri        The URI
     * @param string               $method     The HTTP method
     * @param array                $parameters The query (GET) or request (POST) parameters
     * @param array                $cookies    The request cookies ($_COOKIE)
     * @param array                $files      The request files ($_FILES)
     * @param array                $server     The server parameters ($_SERVER)
     * @param string|resource|null $content    The raw body data
     *
     * @return static
     */
    public static function create($uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null)
    {
        $server = array_replace(array(
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'localhost',
            'HTTP_USER_AGENT' => 'Symfony',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '',
            'SCRIPT_FILENAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_TIME' => time(),
        ), $server);

        $server['PATH_INFO'] = '';
        $server['REQUEST_METHOD'] = strtoupper($method);

        $components = parse_url($uri);
        if (isset($components['host'])) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST'] = $components['host'];
        }

        if (isset($components['scheme'])) {
            if ('https' === $components['scheme']) {
                $server['HTTPS'] = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = 80;
            }
        }

        if (isset($components['port'])) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST'] = $server['HTTP_HOST'].':'.$components['port'];
        }

        if (isset($components['user'])) {
            $server['PHP_AUTH_USER'] = $components['user'];
        }

        if (isset($components['pass'])) {
            $server['PHP_AUTH_PW'] = $components['pass'];
        }

        if (!isset($components['path'])) {
            $components['path'] = '/';
        }

        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if (!isset($server['CONTENT_TYPE'])) {
                    $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                }
                // no break
            case 'PATCH':
                $request = $parameters;
                $query = array();
                break;
            default:
                $request = array();
                $query = $parameters;
                break;
        }

        $queryString = '';
        if (isset($components['query'])) {
            parse_str(html_entity_decode($components['query']), $qs);

            if ($query) {
                $query = array_replace($qs, $query);
                $queryString = http_build_query($query, '', '&');
            } else {
                $query = $qs;
                $queryString = $components['query'];
            }
        } elseif ($query) {
            $queryString = http_build_query($query, '', '&');
        }

        $server['REQUEST_URI'] = $components['path'].('' !== $queryString ? '?'.$queryString : '');
        $server['QUERY_STRING'] = $queryString;

        return self::createRequestFromFactory($query, $request, array(), $cookies, $files, $server, $content);
    }
    
    /**
     * Clones a request and overrides some of its parameters.
     *
     * @param array $query      The GET parameters
     * @param array $request    The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies    The COOKIE parameters
     * @param array $files      The FILES parameters
     * @param array $server     The SERVER parameters
     *
     * @return static
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        $dup = clone $this;
        if (null !== $query) {
            $dup->query = new ParameterBag($query);
        }
        if (null !== $request) {
            $dup->request = new ParameterBag($request);
        }
        if (null !== $attributes) {
            $dup->attributes = new ParameterBag($attributes);
        }
        if (null !== $cookies) {
            $dup->cookies = new ParameterBag($cookies);
        }
        if (null !== $files) {
            $dup->files = new FileBag($files);
        }
        if (null !== $server) {
            $dup->server = new ServerBag($server);
            $dup->headers = new HeaderBag($dup->server->getHeaders());
        }
        $dup->languages = null;
        $dup->charsets = null;
        $dup->encodings = null;
        $dup->acceptableContentTypes = null;
        $dup->pathInfo = null;
        $dup->requestUri = null;
        $dup->baseUrl = null;
        $dup->basePath = null;
        $dup->method = null;
        $dup->format = null;

        if (!$dup->get('_format') && $this->get('_format')) {
            $dup->attributes->set('_format', $this->get('_format'));
        }

        if (!$dup->getRequestFormat(null)) {
            $dup->setRequestFormat($this->getRequestFormat(null));
        }

        return $dup;
    }
    /**
     * Overrides the PHP global variables according to this request instance.
     *
     * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
     * $_FILES is never overridden, see rfc1867
     */
    public function overrideGlobals()
    {
        $this->server->set('QUERY_STRING', static::normalizeQueryString(http_build_query($this->query->toArray(), '', '&')));

        $_GET = $this->query->toArray();
        $_POST = $this->request->toArray();
        $_SERVER = $this->server->toArray();
        $_COOKIE = $this->cookies->toArray();

        foreach ($this->headers->toArray() as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            if (\in_array($key, array('CONTENT_TYPE', 'CONTENT_LENGTH'))) {
                $_SERVER[$key] = implode(', ', $value);
            } else {
                $_SERVER['HTTP_'.$key] = implode(', ', $value);
            }
        }

        $request = array('g' => $_GET, 'p' => $_POST, 'c' => $_COOKIE);

        $requestOrder = ini_get('request_order') ?: ini_get('variables_order');
        $requestOrder = preg_replace('#[^cgp]#', '', strtolower($requestOrder)) ?: 'gp';

        $_REQUEST = array();
        foreach (str_split($requestOrder) as $order) {
            $_REQUEST = array_merge($_REQUEST, $request[$order]);
        }
    }

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @param string $qs Query string
     *
     * @return string A normalized query string for the Request
     */
    public static function normalizeQueryString($qs)
    {
        if ('' == $qs) {
            return '';
        }

        parse_str($qs, $qs);
        ksort($qs);

        return http_build_query($qs, '', '&', PHP_QUERY_RFC3986);
    }
    
    /**
     * Gets a "parameter" value from any bag.
     *
     * This method is mainly useful for libraries that want to provide some flexibility. If you don't need the
     * flexibility in controllers, it is better to explicitly get request parameters from the appropriate
     * public property instead (attributes, query, request).
     *
     * Order of precedence: PATH (routing placeholders or custom attributes), GET, BODY
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this !== $result = $this->attributes->get($key, $this)) {
            return $result;
        }

        if ($this !== $result = $this->query->get($key, $this)) {
            return $result;
        }

        if ($this !== $result = $this->request->get($key, $this)) {
            return $result;
        }

        return $default;
    }
    /**
     * Gets the Session.
     *
     * @return SessionInterface|null The session
     */
    public function getSession()
    {
        $session = $this->session;
        if (!$session instanceof SessionInterface && null !== $session) {
            $this->setSession($session = $session());
        }

        if (null === $session) {
            @trigger_error(sprintf('Calling "%s()" when no session has been set is deprecated since Symfony 4.1 and will throw an exception in 5.0. Use "hasSession()" instead.', __METHOD__), E_USER_DEPRECATED);
            // throw new \BadMethodCallException('Session has not been set');
        }

        return $session;
    }

    /**
     * Whether the request contains a Session which was started in one of the
     * previous requests.
     *
     * @return bool
     */
    public function hasPreviousSession()
    {
        // the check for $this->session avoids malicious users trying to fake a session cookie with proper name
        return $this->hasSession() && $this->cookies->has($this->getSession()->getName());
    }

    /**
     * Whether the request contains a Session object.
     *
     * This method does not give any information about the state of the session object,
     * like whether the session is started or not. It is just a way to check if this Request
     * is associated with a Session instance.
     *
     * @return bool true when the Request contains a Session object, false otherwise
     */
    public function hasSession()
    {
        return null !== $this->session;
    }

    /**
     * Sets the Session.
     *
     * @param SessionInterface $session The Session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }
    /**
     * Returns the client IP addresses.
     *
     * In the returned array the most trusted IP address is first, and the
     * least trusted one last. The "real" client IP address is the last one,
     * but this is also the least trusted one. Trusted proxies are stripped.
     *
     * Use this method carefully; you should use getClientIp() instead.
     *
     * @return array The client IP addresses
     *
     * @see getClientIp()
     */
    public function getClientIps()
    {
        $ip = $this->server->get('REMOTE_ADDR');

        if (!$this->isFromTrustedProxy()) {
            return array($ip);
        }

        return $this->getTrustedValues(self::HEADER_X_FORWARDED_FOR, $ip) ?: array($ip);
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     *
     * @return string|null The client IP address
     *
     * @see getClientIps()
     * @see http://en.wikipedia.org/wiki/X-Forwarded-For
     */
    public function getClientIp()
    {
        $ipAddresses = $this->getClientIps();

        return $ipAddresses[0];
    }
    
    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * @return int|string can be a string if fetched from the server bag
     */
    public function getPort()
    {
        if ($this->isFromTrustedProxy() && $host = $this->getTrustedValues(self::HEADER_X_FORWARDED_PORT)) {
            $host = $host[0];
        } elseif ($this->isFromTrustedProxy() && $host = $this->getTrustedValues(self::HEADER_X_FORWARDED_HOST)) {
            $host = $host[0];
        } elseif (!$host = $this->headers->get('HOST')) {
            return $this->server->get('SERVER_PORT');
        }

        if ('[' === $host[0]) {
            $pos = strpos($host, ':', strrpos($host, ']'));
        } else {
            $pos = strrpos($host, ':');
        }

        if (false !== $pos) {
            return (int) substr($host, $pos + 1);
        }

        return 'https' === $this->getScheme() ? 443 : 80;
    }
    
    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @return string
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return $this->getHost();
        }

        return $this->getHost().':'.$port;
    }

    /**
     * Create new HTTP request.
     *
     * Adds a host header when none was provided and a host is defined in uri.
     *
     * @param string           $method        The request method
     * @param UriInterface     $uri           The request URI object
     * @param HeadersInterface $headers       The request headers collection
     * @param array            $cookies       The request cookies collection
     * @param array            $serverParams  The server environment variables
     * @param StreamInterface  $body          The request body object
     * @param array            $uploadedFiles The request uploadedFiles collection
     * @throws InvalidMethodException on invalid HTTP method
     */
    /*public function __construct(
        $method,
        UriInterface $uri,
        HeadersInterface $headers,
        array $cookies,
        array $serverParams,
        StreamInterface $body,
        array $uploadedFiles = []
    ) {
        try {
            $this->originalMethod = $this->filterMethod($method);
        } catch (InvalidMethodException $e) {
            $this->originalMethod = $method;
        }

        $this->uri = $uri;
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->serverParams = $serverParams;
        $this->attributes = new Collection();
        $this->body = $body;
        $this->uploadedFiles = $uploadedFiles;

        if (isset($serverParams['SERVER_PROTOCOL'])) {
            $this->protocolVersion = str_replace('HTTP/', '', $serverParams['SERVER_PROTOCOL']);
        }

        if (!$this->headers->has('Host') && $this->uri->getHost() !== '') {
            $port = $this->uri->getPort() ? ":{$this->uri->getPort()}" : '';

            $this->headers->set('Host', $this->uri->getHost() . $port);
        }

        $this->registerMediaTypeParser('application/json', function ($input) {
            $result = json_decode($input, true);
            if (!is_array($result)) {
                return null;
            }
            return $result;
        });

        $this->registerMediaTypeParser('application/xml', function ($input) {
            $backup = libxml_disable_entity_loader(true);
            $backup_errors = libxml_use_internal_errors(true);
            $result = simplexml_load_string($input);
            libxml_disable_entity_loader($backup);
            libxml_clear_errors();
            libxml_use_internal_errors($backup_errors);
            if ($result === false) {
                return null;
            }
            return $result;
        });

        $this->registerMediaTypeParser('text/xml', function ($input) {
            $backup = libxml_disable_entity_loader(true);
            $backup_errors = libxml_use_internal_errors(true);
            $result = simplexml_load_string($input);
            libxml_disable_entity_loader($backup);
            libxml_clear_errors();
            libxml_use_internal_errors($backup_errors);
            if ($result === false) {
                return null;
            }
            return $result;
        });

        $this->registerMediaTypeParser('application/x-www-form-urlencoded', function ($input) {
            parse_str($input, $data);
            return $data;
        });

        // if the request had an invalid method, we can throw it now
        if (isset($e) && $e instanceof InvalidMethodException) {
            throw $e;
        }
    }*/

    /**
     * This method is applied to the cloned object
     * after PHP performs an initial shallow-copy. This
     * method completes a deep-copy by creating new objects
     * for the cloned object's internal reference pointers.
     */
    /*public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->attributes = clone $this->attributes;
        $this->body = clone $this->body;
    }*/

    /*******************************************************************************
     * Method
     ******************************************************************************/

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        if ($this->method === null) {
            $this->method = $this->originalMethod;
            $customMethod = $this->getHeaderLine('X-Http-Method-Override');

            if ($customMethod) {
                $this->method = $this->filterMethod($customMethod);
            } elseif ($this->originalMethod === 'POST') {
                $overrideMethod = $this->filterMethod($this->getParsedBodyParam('_METHOD'));
                if ($overrideMethod !== null) {
                    $this->method = $overrideMethod;
                }

                if ($this->getBody()->eof()) {
                    $this->getBody()->rewind();
                }
            }
        }

        return $this->method;
    }

    /**
     * Get the original HTTP method (ignore override).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function getOriginalMethod()
    {
        return $this->originalMethod;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return static
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $method = $this->filterMethod($method);
        $clone = clone $this;
        $clone->originalMethod = $method;
        $clone->method = $method;

        return $clone;
    }

    /**
     * Validate the HTTP method
     *
     * @param  null|string $method
     * @return null|string
     * @throws \InvalidArgumentException on invalid HTTP method.
     */
    protected function filterMethod($method)
    {
        if ($method === null) {
            return $method;
        }

        if (!is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }

        $method = strtoupper($method);
        if (preg_match("/^[!#$%&'*+.^_`|~0-9a-z-]+$/i", $method) !== 1) {
            throw new InvalidMethodException($this, $method);
        }

        return $method;
    }

    /**
     * Does this request use a given method?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param  string $method HTTP method
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getMethod() === $method;
    }

    /**
     * Is this a GET request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod('GET');
    }

    /**
     * Is this a POST request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * Is this a PUT request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod('PUT');
    }

    /**
     * Is this a PATCH request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod('PATCH');
    }

    /**
     * Is this a DELETE request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Is this a HEAD request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isHead()
    {
        return $this->isMethod('HEAD');
    }

    /**
     * Is this a OPTIONS request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isOptions()
    {
        return $this->isMethod('OPTIONS');
    }

    /**
     * Is this an XHR request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isXhr()
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /*******************************************************************************
     * URI
     ******************************************************************************/

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        if ($this->uri === null) {
            return '/';
        }

        $basePath = $this->uri->getBasePath();
        $path = $this->uri->getPath();
        $path = $basePath . '/' . ltrim($path, '/');

        $query = $this->uri->getQuery();
        if ($query) {
            $path .= '?' . $query;
        }
        $this->requestTarget = $path;

        return $this->requestTarget;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target � e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form �
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return static
     * @throws InvalidArgumentException if the request target is invalid
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; must be a string and cannot contain whitespace'
            );
        }
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            if ($uri->getHost() !== '') {
                $clone->headers->set('Host', $uri->getHost());
            }
        } else {
            if ($uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeaderLine('Host') === '')) {
                $clone->headers->set('Host', $uri->getHost());
            }
        }

        return $clone;
    }

    /**
     * Get request content type.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request content type, if known
     */
    public function getContentType()
    {
        $result = $this->getHeader('Content-Type');

        return $result ? $result[0] : null;
    }

    /**
     * Get request media type, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request media type, minus content-type params
     */
    public function getMediaType()
    {
        $contentType = $this->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }

        return null;
    }

    /**
     * Get request media type params, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return array
     */
    public function getMediaTypeParams()
    {
        $contentType = $this->getContentType();
        $contentTypeParams = [];
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);
            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    /**
     * Get request content character set, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getContentCharset()
    {
        $mediaTypeParams = $this->getMediaTypeParams();
        if (isset($mediaTypeParams['charset'])) {
            return $mediaTypeParams['charset'];
        }

        return null;
    }

    /**
     * Get request content length, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return int|null
     */
    public function getContentLength()
    {
        $result = $this->headers->get('Content-Length');

        return $result ? (int)$result[0] : null;
    }

    /*******************************************************************************
     * Cookies
     ******************************************************************************/

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookies;
    }

    /**
     * Fetch cookie value from cookies sent by the client to the server.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key     The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getCookieParam($key, $default = null)
    {
        $cookies = $this->getCookieParams();
        $result = $default;
        if (isset($cookies[$key])) {
            $result = $cookies[$key];
        }

        return $result;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /*******************************************************************************
     * Query Params
     ******************************************************************************/

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        if (is_array($this->queryParams)) {
            return $this->queryParams;
        }

        if ($this->uri === null) {
            return [];
        }

        parse_str($this->uri->getQuery(), $this->queryParams); // <-- URL decodes data

        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *     $_GET.
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /*******************************************************************************
     * File Params
     ******************************************************************************/

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /*******************************************************************************
     * Server Params
     ******************************************************************************/

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Retrieve a server parameter.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getServerParam($key, $default = null)
    {
        $serverParams = $this->getServerParams();

        return isset($serverParams[$key]) ? $serverParams[$key] : $default;
    }

    /*******************************************************************************
     * Attributes
     ******************************************************************************/

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes->toArray();
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes->set($name, $value);

        return $clone;
    }

    /**
     * Create a new instance with the specified derived request attributes.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method allows setting all new derived request attributes as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated attributes.
     *
     * @param  array $attributes New attributes
     * @return static
     */
    public function withAttributes(array $attributes)
    {
        $clone = clone $this;
        $clone->attributes = new Collection($attributes);

        return $clone;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        $clone->attributes->remove($name);

        return $clone;
    }

    /*******************************************************************************
     * Body
     ******************************************************************************/

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     * @throws RuntimeException if the request body media type parser returns an invalid value
     */
    public function getParsedBody()
    {
        if ($this->bodyParsed !== false) {
            return $this->bodyParsed;
        }

        if (!$this->body) {
            return null;
        }

        $mediaType = $this->getMediaType();

        // look for a media type with a structured syntax suffix (RFC 6839)
        $parts = explode('+', $mediaType);
        if (count($parts) >= 2) {
            $mediaType = 'application/' . $parts[count($parts)-1];
        }

        if (isset($this->bodyParsers[$mediaType]) === true) {
            $body = (string)$this->getBody();
            $parsed = $this->bodyParsers[$mediaType]($body);

            if (!is_null($parsed) && !is_object($parsed) && !is_array($parsed)) {
                throw new RuntimeException(
                    'Request body media type parser return value must be an array, an object, or null'
                );
            }
            $this->bodyParsed = $parsed;
            return $this->bodyParsed;
        }

        return null;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {
        if (!is_null($data) && !is_object($data) && !is_array($data)) {
            throw new InvalidArgumentException('Parsed body value must be an array, an object, or null');
        }

        $clone = clone $this;
        $clone->bodyParsed = $data;

        return $clone;
    }

    /**
     * Force Body to be parsed again.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return $this
     */
    public function reparseBody()
    {
        $this->bodyParsed = false;

        return $this;
    }

    /**
     * Register media type parser.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string   $mediaType A HTTP media type (excluding content-type
     *     params).
     * @param callable $callable  A callable that returns parsed contents for
     *     media type.
     */
    public function registerMediaTypeParser($mediaType, callable $callable)
    {
        if ($callable instanceof Closure) {
            $callable = $callable->bindTo($this);
        }
        $this->bodyParsers[(string)$mediaType] = $callable;
    }

    /*******************************************************************************
     * Parameters (e.g., POST and GET data)
     ******************************************************************************/

    /**
     * Fetch request parameter value from body or query string (in that order).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param  string $key The parameter key.
     * @param  string $default The default value.
     *
     * @return mixed The parameter value.
     */
    public function getParam($key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $getParams = $this->getQueryParams();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * Fetch parameter value from request body.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParsedBodyParam($key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        }

        return $result;
    }

    /**
     * Fetch parameter value from query string.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getQueryParam($key, $default = null)
    {
        $getParams = $this->getQueryParams();
        $result = $default;
        if (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * Fetch associative array of body and query string parameters.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param array|null $only list the keys to retrieve.
     * @return array|null
     */
    public function getParams(array $only = null)
    {
        $params = $this->getQueryParams();
        $postParams = $this->getParsedBody();
        if ($postParams) {
            $params = array_merge($params, (array)$postParams);
        }

        if ($only) {
            $onlyParams = [];
            foreach ($only as $key) {
                if (array_key_exists($key, $params)) {
                    $onlyParams[$key] = $params[$key];
                }
            }
            return $onlyParams;
        }

        return $params;
    }
}
