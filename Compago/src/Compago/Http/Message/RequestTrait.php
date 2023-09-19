<?php

namespace Compago\Http\Message;

use Exception;

trait RequestTrait
{
    protected $method;
    /**
     * Gets the HTTP method.
     *
     * The method is always an uppercased string.
     *
     * @return string The HTTP method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the HTTP method.
     *
     * @param string $method The HTTP method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);

        return $this;
    }
}
trait ___RequestTrait
{
    
    
    protected $baseUrl;
    protected $pathInfo;
    protected $host;
    protected $scheme;
    protected $httpPort;
    protected $httpsPort;
    protected $queryString;
    protected $parameters = array();
    
    /**
     * Gets the base URL.
     *
     * @return string The base URL
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Sets the base URL.
     *
     * @param string $baseUrl The base URL
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Gets the path info.
     *
     * @return string The path info
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * Sets the path info.
     *
     * @param string $pathInfo The path info
     *
     * @return $this
     */
    public function setPathInfo($pathInfo)
    {
        $this->pathInfo = $pathInfo;

        return $this;
    }

    

    /**
     * Gets the HTTP host.
     *
     * The host is always lowercased because it must be treated case-insensitive.
     *
     * @return string The HTTP host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the HTTP host.
     *
     * @param string $host The HTTP host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = strtolower($host);

        return $this;
    }

    /**
     * Gets the HTTP scheme.
     *
     * @return string The HTTP scheme
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Sets the HTTP scheme.
     *
     * @param string $scheme The HTTP scheme
     *
     * @return $this
     */
    public function setScheme($scheme)
    {
        $this->scheme = strtolower($scheme);

        return $this;
    }

    /**
     * Gets the HTTP port.
     *
     * @return int The HTTP port
     */
    public function getHttpPort()
    {
        return $this->httpPort;
    }

    /**
     * Sets the HTTP port.
     *
     * @param int $httpPort The HTTP port
     *
     * @return $this
     */
    public function setHttpPort($httpPort)
    {
        $this->httpPort = (int) $httpPort;

        return $this;
    }

    /**
     * Gets the HTTPS port.
     *
     * @return int The HTTPS port
     */
    public function getHttpsPort()
    {
        return $this->httpsPort;
    }

    /**
     * Sets the HTTPS port.
     *
     * @param int $httpsPort The HTTPS port
     *
     * @return $this
     */
    public function setHttpsPort($httpsPort)
    {
        $this->httpsPort = (int) $httpsPort;

        return $this;
    }

    /**
     * Gets the query string.
     *
     * @return string The query string without the "?"
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Sets the query string.
     *
     * @param string $queryString The query string (after "?")
     *
     * @return $this
     */
    public function setQueryString($queryString)
    {
        // string cast to be fault-tolerant, accepting null
        $this->queryString = (string) $queryString;

        return $this;
    }

    /**
     * Returns the parameters.
     *
     * @return array The parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets the parameters.
     *
     * @param array $parameters The parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Gets a parameter value.
     *
     * @param string $name A parameter name
     *
     * @return mixed The parameter value or null if nonexistent
     */
    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    /**
     * Checks if a parameter value is set for the given parameter.
     *
     * @param string $name A parameter name
     *
     * @return bool True if the parameter value is set, false otherwise
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Sets a parameter value.
     *
     * @param string $name      A parameter name
     * @param mixed  $parameter The parameter value
     *
     * @return $this
     */
    public function setParameter($name, $parameter)
    {
        $this->parameters[$name] = $parameter;

        return $this;
    }

}
