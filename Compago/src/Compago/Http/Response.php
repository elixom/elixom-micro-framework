<?php
namespace Compago\Http;

use ArrayObject;
use JsonSerializable;
use \Compago\Http\Message\MessageTrait;
use \Compago\Http\Message\ResponseTrait;
use \Compago\Http\ResponseHeaderBag;
use \Compago\Contracts\Arrayable;
use \Compago\Contracts\Jsonable;
use \Compago\Contracts\Renderable;


function redirect($url,$status=302,$headers=array()){
    return new RedirectResponse($url,$status,$headers);
}
if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param  \Illuminate\View\View|string|array|null  $content
     * @param  int     $status
     * @param  array   $headers
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }
}


class Response implements ResponseInterface{
    use MessageTrait;
    use ResponseTrait;
    
    /** @var array Map of standard HTTP status code/reason phrases */
    private static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /** @var int */
    protected $statusCode = 200;

    /** @var string */
    protected $reasonPhrase = '';
    /** @var string */
    protected $charset;
    
    /** @var ResponseHeaderBag Map of all registered headers, as original name => array of values */
    protected $headers;

    /**
     * @param int                                  $status  Status code
     * @param array|ResponseHeaderBag              $headers Response headers
     * @param string|null|resource                 $body    Response body
     * @param string                               $version Protocol version
     * @param string|null                          $reason  Reason phrase (when empty a default will be used based on the status code)
     */
    public function __construct(
        $body = null,
        $status = 200,
        array $headers = [],
        $version = '1.1',
        $reason = null
    ) {
        $this->setStatus($status, $reason);
        
        if ($body !== '' && $body !== null) {
            $this->setBody($body);
        }
        $this->setHeaders($headers);
        
        $this->setProtocolVersion($version);
    }
    /**
     * Factory method for chainability.
     *
     * Example:
     *
     *     return Response::create($body, 200)
     *         ->setSharedMaxAge(300);
     *
     * @param mixed $content The response content, see setBody()
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return static
     */
    public static function create($content = '', $status = 200, $headers = array())
    {
        return new static($content, $status, $headers);
    }
    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     *
     * @return string The Response as an HTTP string
     *
     * @see prepare()
     */
    public function __toString()
    {
        return
            sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->reasonPhrase)."\r\n".
            $this->headers."\r\n".
            $this->getBody();
    }
    /**
     * Clones the current Response instance.
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }
    /**
     * Sets the status.
     *
     * @param string|integer 
     * @param reason
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When the callback name is not valid
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $code = (int) $code;
        if (($code < 100) || $code >999){
            throw new \InvalidArgumentException('The status code is invalid');
        }
        $new = clone $this;
        $new->statusCode = $code;
        if ($reasonPhrase == '' && isset(self::$phrases[$new->statusCode])) {
            $reasonPhrase = self::$phrases[$new->statusCode];
        }
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }
    public function setStatus($code, $reasonPhrase = '')
    {
        $code = (int) $code;
        if (($code < 100) || $code >999){
            throw new \InvalidArgumentException('The status code is invalid');
        }
        $this->statusCode = $code;
        if ($reasonPhrase == '' && isset(self::$phrases[$this->statusCode])) {
            $reasonPhrase = self::$phrases[$this->statusCode];
        }
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if (($code < 100) || $code >999){
            throw new \InvalidArgumentException('The status code is invalid');
        }
        $this->statusCode = $code;
        return $this;
    }
    public function setHeaders($headers)
    {
        $this->headers = new ResponseHeaderBag($headers);
    }
    /**
     * Set the content on the response.
     *
     * Valid types are strings, numbers, null, and objects that implement a __toString() method.
     *
     * @param mixed $content Content that can be cast to string
     *
     * @return $this
     *
     * @throws \UnexpectedValueException
     */
    public function setBody($content)
    {
        $this->original = $content;
        if ($content === null){
            $this->body = $content;
            return $this;
        }
        // If the content is "JSONable" we will set the appropriate header and convert
        // the content to JSON. This is useful when returning something like models
        // from routes that will be automatically transformed to their JSON form.
        
        if ($this->shouldBeJson($content)) {
            $this->header('Content-Type', 'application/json');
            $content = $this->morphToJson($content);
            
        }

        // If this content implements the "Renderable" interface then we will call the
        // render method on the object so we will avoid any "__toString" exceptions
        // that might be thrown and have their errors obscured by PHP's handling.
        elseif ($content instanceof Renderable) {
            $content = $content->render();
        }
        if (null !== $content && !\is_string($content) && !is_numeric($content) && !\is_callable(array($content, '__toString'))) {
            throw new \UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', \gettype($content)));
        }
        
        $this->body = (string) $content;
        
        return $this;
    }
    public function setField($field,$content=null){
        if ($this->original === null){
            $this->original = array();
            $this->header('Content-Type', 'application/json');
        }
        if (is_array($this->original) ){
            if (func_num_args() ==1){
                if (is_array($field)){
                    foreach($field as $k=>$v){
                        $this->original[$k] = $v;
                    } 
                } else {
                    $this->original[$field] = '';
                }
            } else {
                $this->original[$field] = $content;
            }
            $this->body = json_encode($this->original);
        }    
        return $this;
    }
    
    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof Arrayable ||
               $content instanceof Jsonable ||
               $content instanceof ArrayObject ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }

    /**
     * Morph the given content into JSON.
     *
     * @param  mixed   $content
     * @return string
     */
    protected function morphToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        } elseif ($content instanceof Arrayable) {
            return json_encode($content->toArray());
        }

        return json_encode($content);
    }
    
    
    /**
     * Prepares the Response before it is sent to the client.
     *
     * This method tweaks the Response to ensure that it is
     * compliant with RFC 2616. Most of the changes are based on
     * the Request that is "associated" with this Response.
     *
     * @return $this
     */
    public function prepare(Request $request)
    {
        $headers = $this->headers;

        if ($this->isInformational() || $this->isEmpty()) {
            $this->setBody(null);
            $headers->remove('Content-Type');
            $headers->remove('Content-Length');
        } else {
            // Content-type based on the Request
            if (!$headers->has('Content-Type')) {
                $format = $request->getRequestFormat();
                if (null !== $format && $mimeType = $request->getMimeType($format)) {
                    $headers->set('Content-Type', $mimeType);
                }
            }

            // Fix Content-Type
            $charset = $this->charset ?: 'UTF-8';
            if (!$headers->has('Content-Type')) {
                $headers->set('Content-Type', 'text/html; charset='.$charset);
            } elseif (0 === stripos($headers->get('Content-Type'), 'text/') && false === stripos($headers->get('Content-Type'), 'charset')) {
                // add the charset
                $headers->set('Content-Type', $headers->get('Content-Type').'; charset='.$charset);
            }

            // Fix Content-Length
            if ($headers->has('Transfer-Encoding')) {
                $headers->remove('Content-Length');
            }

            if ($request->isMethod('HEAD')) {
                // cf. RFC2616 14.13
                $length = $headers->get('Content-Length');
                $this->setBody(null);
                if ($length) {
                    $headers->set('Content-Length', $length);
                }
            }
        }

        // Fix protocol
        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        // Check if we need to send extra expire info headers
        if ('1.0' == $this->getProtocolVersion() && false !== strpos($this->headers->get('Cache-Control'), 'no-cache')) {
            $this->headers->set('pragma', 'no-cache');
            $this->headers->set('expires', -1);
        }

        $this->ensureIEOverSSLCompatibility($request);

        return $this;
    }
    
    /**
     * Sends HTTP headers.
     *
     * @return $this
     */
    public function sendHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        // headers
        foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            foreach ($values as $value) {
                header($name.': '.$value, false, $this->statusCode);
            }
        }

        // cookies
        foreach ($this->headers->getCookies() as $cookie) {
            header('Set-Cookie: '.$cookie->getName().strstr($cookie, '='), false, $this->statusCode);
        }

        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->reasonPhrase), true, $this->statusCode);
        return $this;
    }
    /**
     * Sends content for the current web response.
     *
     * @return $this
     */
    public function sendBody()
    {
        echo $this->body;

        return $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return $this
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();

        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (!\in_array(\PHP_SAPI, array('cli', 'phpdbg'), true)) {
            static::closeOutputBuffers(0, true);
        }

        return $this;
    }
    
    
    
    
    
    
    
    
    
    
    /**
     * Sets the response charset.
     *
     * @return $this
     *
     * @final
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Retrieves the response charset.
     *
     * @final
     */
    public function getCharset(): ?string
    {
        return $this->charset;
    }

    /**
     * Returns true if the response may safely be kept in a shared (surrogate) cache.
     *
     * Responses marked "private" with an explicit Cache-Control directive are
     * considered uncacheable.
     *
     * Responses with neither a freshness lifetime (Expires, max-age) nor cache
     * validator (Last-Modified, ETag) are considered uncacheable because there is
     * no way to tell when or how to remove them from the cache.
     *
     * Note that RFC 7231 and RFC 7234 possibly allow for a more permissive implementation,
     * for example "status codes that are defined as cacheable by default [...]
     * can be reused by a cache with heuristic expiration unless otherwise indicated"
     * (https://tools.ietf.org/html/rfc7231#section-6.1)
     *
     * @final
     */
    public function isCacheable(): bool
    {
        if (!\in_array($this->statusCode, array(200, 203, 300, 301, 302, 404, 410))) {
            return false;
        }

        if ($this->headers->hasCacheControlDirective('no-store') || $this->headers->getCacheControlDirective('private')) {
            return false;
        }

        return $this->isValidateable() || $this->isFresh();
    }

    /**
     * Returns true if the response is "fresh".
     *
     * Fresh responses may be served from cache without any interaction with the
     * origin. A response is considered fresh when it includes a Cache-Control/max-age
     * indicator or Expires header and the calculated age is less than the freshness lifetime.
     *
     * @final
     */
    public function isFresh(): bool
    {
        return $this->getTtl() > 0;
    }

    /**
     * Returns true if the response includes headers that can be used to validate
     * the response with the origin server using a conditional GET request.
     *
     * @final
     */
    public function isValidateable(): bool
    {
        return $this->headers->has('Last-Modified') || $this->headers->has('ETag');
    }

    /**
     * Marks the response as "private".
     *
     * It makes the response ineligible for serving other clients.
     *
     * @return $this
     *
     * @final
     */
    public function setPrivate()
    {
        $this->headers->removeCacheControlDirective('public');
        $this->headers->addCacheControlDirective('private');

        return $this;
    }

    /**
     * Marks the response as "public".
     *
     * It makes the response eligible for serving other clients.
     *
     * @return $this
     *
     * @final
     */
    public function setPublic()
    {
        $this->headers->addCacheControlDirective('public');
        $this->headers->removeCacheControlDirective('private');

        return $this;
    }

    /**
     * Marks the response as "immutable".
     *
     * @return $this
     *
     * @final
     */
    public function setImmutable(bool $immutable = true)
    {
        if ($immutable) {
            $this->headers->addCacheControlDirective('immutable');
        } else {
            $this->headers->removeCacheControlDirective('immutable');
        }

        return $this;
    }

    /**
     * Returns true if the response is marked as "immutable".
     *
     * @final
     */
    public function isImmutable(): bool
    {
        return $this->headers->hasCacheControlDirective('immutable');
    }

    /**
     * Returns true if the response must be revalidated by caches.
     *
     * This method indicates that the response must not be served stale by a
     * cache in any circumstance without first revalidating with the origin.
     * When present, the TTL of the response should not be overridden to be
     * greater than the value provided by the origin.
     *
     * @final
     */
    public function mustRevalidate(): bool
    {
        return $this->headers->hasCacheControlDirective('must-revalidate') || $this->headers->hasCacheControlDirective('proxy-revalidate');
    }

    /**
     * Returns the Date header as a DateTime instance.
     *
     * @throws \RuntimeException When the header is not parseable
     *
     * @final
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->headers->getDate('Date');
    }

    /**
     * Sets the Date header.
     *
     * @return $this
     *
     * @final
     */
    public function setDate(\DateTimeInterface $date)
    {
        if ($date instanceof \DateTime) {
            $date = \DateTimeImmutable::createFromMutable($date);
        }

        $date = $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Date', $date->format('D, d M Y H:i:s').' GMT');

        return $this;
    }

    /**
     * Returns the age of the response in seconds.
     *
     * @final
     */
    public function getAge(): int
    {
        if (null !== $age = $this->headers->get('Age')) {
            return (int) $age;
        }

        return max(time() - $this->getDate()->format('U'), 0);
    }

    /**
     * Marks the response stale by setting the Age header to be equal to the maximum age of the response.
     *
     * @return $this
     */
    public function expire()
    {
        if ($this->isFresh()) {
            $this->headers->set('Age', $this->getMaxAge());
        }

        return $this;
    }

    /**
     * Returns the value of the Expires header as a DateTime instance.
     *
     * @final
     */
    public function getExpires(): ?\DateTimeInterface
    {
        try {
            return $this->headers->getDate('Expires');
        } catch (\RuntimeException $e) {
            // according to RFC 2616 invalid date formats (e.g. "0" and "-1") must be treated as in the past
            return \DateTime::createFromFormat('U', time() - 172800);
        }
    }

    /**
     * Sets the Expires HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @return $this
     *
     * @final
     */
    public function setExpires(\DateTimeInterface $date = null)
    {
        if (null === $date) {
            $this->headers->remove('Expires');

            return $this;
        }

        if ($date instanceof \DateTime) {
            $date = \DateTimeImmutable::createFromMutable($date);
        }

        $date = $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Expires', $date->format('D, d M Y H:i:s').' GMT');

        return $this;
    }

    /**
     * Returns the number of seconds after the time specified in the response's Date
     * header when the response should no longer be considered fresh.
     *
     * First, it checks for a s-maxage directive, then a max-age directive, and then it falls
     * back on an expires header. It returns null when no maximum age can be established.
     *
     * @final
     */
    public function getMaxAge(): ?int
    {
        if ($this->headers->hasCacheControlDirective('s-maxage')) {
            return (int) $this->headers->getCacheControlDirective('s-maxage');
        }

        if ($this->headers->hasCacheControlDirective('max-age')) {
            return (int) $this->headers->getCacheControlDirective('max-age');
        }

        if (null !== $this->getExpires()) {
            return (int) ($this->getExpires()->format('U') - $this->getDate()->format('U'));
        }

        return null;
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh.
     *
     * This methods sets the Cache-Control max-age directive.
     *
     * @return $this
     *
     * @final
     */
    public function setMaxAge(int $value)
    {
        $this->headers->addCacheControlDirective('max-age', $value);

        return $this;
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh by shared caches.
     *
     * This methods sets the Cache-Control s-maxage directive.
     *
     * @return $this
     *
     * @final
     */
    public function setSharedMaxAge(int $value)
    {
        $this->setPublic();
        $this->headers->addCacheControlDirective('s-maxage', $value);

        return $this;
    }

    /**
     * Returns the response's time-to-live in seconds.
     *
     * It returns null when no freshness information is present in the response.
     *
     * When the responses TTL is <= 0, the response may not be served from cache without first
     * revalidating with the origin.
     *
     * @final
     */
    public function getTtl(): ?int
    {
        $maxAge = $this->getMaxAge();

        return null !== $maxAge ? $maxAge - $this->getAge() : null;
    }

    /**
     * Sets the response's time-to-live for shared caches in seconds.
     *
     * This method adjusts the Cache-Control/s-maxage directive.
     *
     * @return $this
     *
     * @final
     */
    public function setTtl(int $seconds)
    {
        $this->setSharedMaxAge($this->getAge() + $seconds);

        return $this;
    }

    /**
     * Sets the response's time-to-live for private/client caches in seconds.
     *
     * This method adjusts the Cache-Control/max-age directive.
     *
     * @return $this
     *
     * @final
     */
    public function setClientTtl(int $seconds)
    {
        $this->setMaxAge($this->getAge() + $seconds);

        return $this;
    }

    /**
     * Returns the Last-Modified HTTP header as a DateTime instance.
     *
     * @throws \RuntimeException When the HTTP header is not parseable
     *
     * @final
     */
    public function getLastModified(): ?\DateTimeInterface
    {
        return $this->headers->getDate('Last-Modified');
    }

    /**
     * Sets the Last-Modified HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @return $this
     *
     * @final
     */
    public function setLastModified(\DateTimeInterface $date = null)
    {
        if (null === $date) {
            $this->headers->remove('Last-Modified');

            return $this;
        }

        if ($date instanceof \DateTime) {
            $date = \DateTimeImmutable::createFromMutable($date);
        }

        $date = $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Last-Modified', $date->format('D, d M Y H:i:s').' GMT');

        return $this;
    }

    /**
     * Returns the literal value of the ETag HTTP header.
     *
     * @final
     */
    public function getEtag(): ?string
    {
        return $this->headers->get('ETag');
    }

    /**
     * Sets the ETag value.
     *
     * @param string|null $etag The ETag unique identifier or null to remove the header
     * @param bool        $weak Whether you want a weak ETag or not
     *
     * @return $this
     *
     * @final
     */
    public function setEtag(string $etag = null, bool $weak = false)
    {
        if (null === $etag) {
            $this->headers->remove('Etag');
        } else {
            if (0 !== strpos($etag, '"')) {
                $etag = '"'.$etag.'"';
            }

            $this->headers->set('ETag', (true === $weak ? 'W/' : '').$etag);
        }

        return $this;
    }

    /**
     * Sets the response's cache headers (validation and/or expiration).
     *
     * Available options are: etag, last_modified, max_age, s_maxage, private, public and immutable.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     *
     * @final
     */
    public function setCache(array $options)
    {
        if ($diff = array_diff(array_keys($options), array('etag', 'last_modified', 'max_age', 's_maxage', 'private', 'public', 'immutable'))) {
            throw new \InvalidArgumentException(sprintf('Response does not support the following options: "%s".', implode('", "', array_values($diff))));
        }

        if (isset($options['etag'])) {
            $this->setEtag($options['etag']);
        }

        if (isset($options['last_modified'])) {
            $this->setLastModified($options['last_modified']);
        }

        if (isset($options['max_age'])) {
            $this->setMaxAge($options['max_age']);
        }

        if (isset($options['s_maxage'])) {
            $this->setSharedMaxAge($options['s_maxage']);
        }

        if (isset($options['public'])) {
            if ($options['public']) {
                $this->setPublic();
            } else {
                $this->setPrivate();
            }
        }

        if (isset($options['private'])) {
            if ($options['private']) {
                $this->setPrivate();
            } else {
                $this->setPublic();
            }
        }

        if (isset($options['immutable'])) {
            $this->setImmutable((bool) $options['immutable']);
        }

        return $this;
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a 304 status code.
     *
     * This sets the status, removes the body, and discards any headers
     * that MUST NOT be included in 304 responses.
     *
     * @return $this
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3.5
     *
     * @final
     */
    public function setNotModified()
    {
        $this->setStatusCode(304);
        $this->setBody(null);

        // remove headers that MUST NOT be included with 304 Not Modified responses
        foreach (array('Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified') as $header) {
            $this->headers->remove($header);
        }

        return $this;
    }

    /**
     * Returns true if the response includes a Vary header.
     *
     * @final
     */
    public function hasVary(): bool
    {
        return null !== $this->headers->get('Vary');
    }

    /**
     * Returns an array of header names given in the Vary header.
     *
     * @final
     */
    public function getVary(): array
    {
        if (!$vary = $this->headers->get('Vary', null, false)) {
            return array();
        }

        $ret = array();
        foreach ($vary as $item) {
            $ret = array_merge($ret, preg_split('/[\s,]+/', $item));
        }

        return $ret;
    }

    /**
     * Sets the Vary header.
     *
     * @param string|array $headers
     * @param bool         $replace Whether to replace the actual value or not (true by default)
     *
     * @return $this
     *
     * @final
     */
    public function setVary($headers, bool $replace = true)
    {
        $this->headers->set('Vary', $headers, $replace);

        return $this;
    }

    /**
     * Determines if the Response validators (ETag, Last-Modified) match
     * a conditional value specified in the Request.
     *
     * If the Response is not modified, it sets the status code to 304 and
     * removes the actual content by calling the setNotModified() method.
     *
     * @return bool true if the Response validators match the Request, false otherwise
     *
     * @final
     */
    public function isNotModified(Request $request): bool
    {
        if (!$request->isMethodCacheable()) {
            return false;
        }

        $notModified = false;
        $lastModified = $this->headers->get('Last-Modified');
        $modifiedSince = $request->headers->get('If-Modified-Since');

        if ($etags = $request->getETags()) {
            $notModified = \in_array($this->getEtag(), $etags) || \in_array('*', $etags);
        }

        if ($modifiedSince && $lastModified) {
            $notModified = strtotime($modifiedSince) >= strtotime($lastModified) && (!$etags || $notModified);
        }

        if ($notModified) {
            $this->setNotModified();
        }

        return $notModified;
    }

    /**
     * Is response invalid?
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @final
     */
    public function isInvalid(): bool
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is response informative?
     *
     * @final
     */
    public function isInformational(): bool
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is response successful?
     *
     * @final
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is the response a redirect?
     *
     * @final
     */
    public function isRedirection(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is there a client error?
     *
     * @final
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Was there a server side error?
     *
     * @final
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     *
     * @final
     */
    public function isOk(): bool
    {
        return 200 === $this->statusCode;
    }

    /**
     * Is the response forbidden?
     *
     * @final
     */
    public function isForbidden(): bool
    {
        return 403 === $this->statusCode;
    }

    /**
     * Is the response a not found error?
     *
     * @final
     */
    public function isNotFound(): bool
    {
        return 404 === $this->statusCode;
    }

    /**
     * Is the response a redirect of some form?
     *
     * @final
     */
    public function isRedirect(string $location = null): bool
    {
        return \in_array($this->statusCode, array(201, 301, 302, 303, 307, 308)) && (null === $location ?: $location == $this->headers->get('Location'));
    }

    /**
     * Is the response empty?
     *
     * @final
     */
    public function isEmpty(): bool
    {
        return \in_array($this->statusCode, array(204, 304));
    }

    /**
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     *
     * @final
     */
    public static function closeOutputBuffers(int $targetLevel, bool $flush)
    {
        $status = ob_get_status(true);
        $level = \count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    /**
     * Checks if we need to remove Cache-Control for SSL encrypted downloads when using IE < 9.
     *
     * @see http://support.microsoft.com/kb/323308
     *
     * @final
     */
    protected function ensureIEOverSSLCompatibility(Request $request)
    {
        if (false !== stripos($this->headers->get('Content-Disposition'), 'attachment') && 1 == preg_match('/MSIE (.*?);/i', $request->server->get('HTTP_USER_AGENT'), $match) && true === $request->isSecure()) {
            if ((int) preg_replace('/(MSIE )(.*?);/', '$2', $match[0]) < 9) {
                $this->headers->remove('Cache-Control');
            }
        }
    }
}