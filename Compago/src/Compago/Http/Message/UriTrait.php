<?php

namespace Compago\Http\Message;


use Exception;
use InvalidArgumentException;

trait UriTrait
{
    /**
     * Uri scheme (without "://" suffix)
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * Uri user
     *
     * @var string
     */
    protected $user = '';

    /**
     * Uri password
     *
     * @var string
     */
    protected $password = '';

    /**
     * Uri host
     *
     * @var string
     */
    protected $host = '';

    /**
     * Uri port number
     *
     * @var null|int
     */
    protected $port;

    /**
     * Uri base path
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * Uri path
     *
     * @var string
     */
    protected $path = '';

    /**
     * Uri query string (without "?" prefix)
     *
     * @var string
     */
    protected $query = '';

    /**
     * Uri fragment string (without "#" prefix)
     *
     * @var string
     */
    protected $fragment = '';


    /********************************************************************************
     * Scheme
     *******************************************************************************/

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme;
    }
    
    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return self A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /**
     * Filter Uri scheme.
     *
     * @param  string $scheme Raw Uri scheme.
     * @return string
     *
     * @throws InvalidArgumentException If the Uri scheme is not a string.
     * @throws InvalidArgumentException If Uri scheme is not "", "https", or "http".
     */
    protected function filterScheme($scheme)
    {
        static $valid = [
            '' => true,
            'https' => true,
            'http' => true,
        ];

        if (!is_string($scheme) && !method_exists($scheme, '__toString')) {
            throw new InvalidArgumentException('Uri scheme must be a string');
        }

        $scheme = str_replace('://', '', strtolower((string)$scheme));
        if (!isset($valid[$scheme])) {
            throw new InvalidArgumentException('Uri scheme must be one of: "", "https", "http"');
        }

        return $scheme;
    }

    /********************************************************************************
     * Authority
     *******************************************************************************/

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        return ($userInfo ? $userInfo . '@' : '') . $host . ($port !== null ? ':' . $port : '');
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return $this->user . ($this->password ? ':' . $this->password : '');
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user The user name to use for authority.
     * @param null|string $password The password associated with $user.
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $this->filterUserInfo($user);
        if ($clone->user) {
            $clone->password = $password ? $this->filterUserInfo($password) : '';
        } else {
            $clone->password = '';
        }

        return $clone;
    }

    /**
     * Filters the user info string.
     *
     * @param string $query The raw uri query string.
     * @return string The percent-encoded query string.
     */
    protected function filterUserInfo($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=]+|%(?![A-Fa-f0-9]{2}))/u',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     * @return self A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->port && !$this->hasStandardPort() ? $this->port : null;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *     removes the port information.
     * @return self A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        $port = $this->filterPort($port);
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * Does this Uri use a standard port?
     *
     * @return bool
     */
    protected function hasStandardPort()
    {
        return ($this->scheme === 'http' && $this->port === 80) || ($this->scheme === 'https' && $this->port === 443);
    }

    /**
     * Filter Uri port.
     *
     * @param  null|int $port The Uri port number.
     * @return null|int
     *
     * @throws InvalidArgumentException If the port is invalid.
     */
    protected function filterPort($port)
    {
        if (is_string($port)){
            $port = (int)$port;
        }
        if (is_null($port) || (is_integer($port) && ($port >= 1 && $port <= 65535))) {
            return $port;
        }

        throw new InvalidArgumentException('Uri port must be null or an integer between 1 and 65535 (inclusive)');
    }

    /********************************************************************************
     * Path
     *******************************************************************************/

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     * @return self A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Uri path must be a string');
        }

        $clone = clone $this;
        $clone->path = $this->filterPath($path);

        // if the path is absolute, then clear basePath
        if (substr($path, 0, 1) == '/') {
            $clone->basePath = '';
        }

        return $clone;
    }

    /**
     * Retrieve the base path segment of the URI.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method MUST return a string; if no path is present it MUST return
     * an empty string.
     *
     * @return string The base path segment of the URI.
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set base path.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param  string $basePath
     * @return self
     */
    public function withBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw new InvalidArgumentException('Uri path must be a string');
        }
        if (!empty($basePath)) {
            $basePath = '/' . trim($basePath, '/'); // <-- Trim on both sides
        }
        $clone = clone $this;

        if ($basePath !== '/') {
            $clone->basePath = $this->filterPath($basePath);
        }

        return $clone;
    }

    /**
     * Filter Uri path.
     *
     * This method percent-encodes all reserved
     * characters in the provided path string. This method
     * will NOT double-encode characters that are already
     * percent-encoded.
     *
     * @param  string $path The raw uri path.
     * @return string       The RFC 3986 percent-encoded uri path.
     * @link   http://www.faqs.org/rfcs/rfc3986.html
     */
    protected function filterPath($path)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    /********************************************************************************
     * Query
     *******************************************************************************/

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     * @return self A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        if (is_array($query)){
            $query = http_build_query($query);
        }
        if (!is_string($query) && !method_exists($query, '__toString')) {
            throw new InvalidArgumentException('Uri query must be a string');
        }
        $query = ltrim((string)$query, '?');
        $clone = clone $this;
        $clone->query = $this->filterQuery($query);

        return $clone;
    }

    /**
     * Filters the query string or fragment of a URI.
     *
     * @param string $query The raw uri query string.
     * @return string The percent-encoded query string.
     */
    protected function filterQuery($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    /********************************************************************************
     * Fragment
     *******************************************************************************/

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     * @return self A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        if (!is_string($fragment) && !method_exists($fragment, '__toString')) {
            throw new InvalidArgumentException('Uri fragment must be a string');
        }
        $fragment = ltrim((string)$fragment, '#');
        $clone = clone $this;
        $clone->fragment = $this->filterQuery($fragment);

        return $clone;
    }

    /********************************************************************************
     * Helpers
     *******************************************************************************/

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $basePath = $this->getBasePath();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        $path = $basePath . '/' . ltrim($path, '/');

        return ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . $path
            . ($query ? '?' . $query : '')
            . ($fragment ? '#' . $fragment : '');
    }

    /**
     * Return the fully qualified base URL.
     *
     * Note that this method never includes a trailing /
     *
     * This method is not part of PSR-7.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $basePath = $this->getBasePath();

        if ($authority && substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        return ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . rtrim($basePath, '/');
    }
    /**
     * Remove segments from the path
     * return number of removved segments
     * 
     */
    public function removeSegment($anyof=array()){
        if(!is_array($anyof)){
            if(func_num_args()>1){
                $anyof = func_get_args();
            }else{
                $anyof =array($anyof);
            }
        }
        $anyof = array_filter($anyof);
        $i = 0;
        if(!empty($this->path) && count($anyof)){
            $segments = explode('/',$this->path);
            foreach($segments as $k=>$v){
                if(in_array($v,$anyof)){
                    unset($segments[$k]);
                    $i++;
                }
            }
            if($i){
                $this->path = implode('/',$segments);
            }
        }
        return $i;
    }
    /**
     * Check if a path contains a segments or array of segment
     * return boolean
     * 
     */
    public function hasSegment($anyof=array()){

        if(!is_array($anyof)){
            if(func_num_args()>1){
                $anyof = func_get_args();
            }else{
                $anyof =array($anyof);
            }
        }
        $anyof = array_filter($anyof);
        $i = 0;
        if(!empty($this->path) && count($anyof)){
            $segments = explode('/',$this->path);
            foreach($segments as $v){
                if(in_array($v,$anyof)){
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Check if a path contains a segment
     * return index of segment
     * 
     */
    public function findSegment($segment){
        //look up and return the location of matching segment
        if(!empty($this->path) && $segment){
            $segments = explode('/',$this->path);
            foreach($segments as $k=>$v){
                if($v == $segment){
                    return $k;
                }
            }
        }
        return false;
    }
    /**
     * Check if a path contains a segment
     * return index of segment
     * 
     */
    public function getSegment($index, $default=false){
        //look up and return the location of matching segment
        if(!empty($this->path) ){
            $segments = explode('/',$this->path);
            if (is_int($index)){
                if (isset($segments[$index])){
                    return $segments[$index];
                }
                return $default;
            }
            $i = $this->findSegment($index);
            if ($i !== false){
                $index = $i + 1;
                if (isset($segments[$index])){
                    return $segments[$index];
                }
                return $default;
            }
        }
        return $default;
    }
    
    /**
     * Check if a path contains a segment
     * return index of segment
     * 
     */
    function getSegmentAssoc($name, $default=false) {
        $segments = explode('/',$this->path);
        $location = $this->findSegment($name);
        if($location === false){
            return $default;
        }
        $location+=1;

        if(isset($segments[$location])){
            return $segments[$location];
        }
        return $default;

    }
    /**
     * 
     * return associative array of segments
     * 
     */
    function segmentToAssoc($name, $default=false) {
        $segments = explode('/',ltrim($this->path,'/'));
        $e = count($segments);
        $ret = [];
        for($i = 0; $i < $e; $i+=2){
            $k = $segments[$i];
            $i2 = $i+1;
            if (isset($segments[$i2])){
                $ret[$k] = $segments[$i];
            } else {
                $ret[$k] = null;
            }
        }
        return $ret;

    }
    /**
     * 
     * return array of segments
     * 
     */
    function segmentToArray($name, $default=false) {
        $segments = explode('/',ltrim($this->path,'/'));
        return $segments;
    }
     
}   
