<?php
namespace Compago\Http\Message;
use \Compago\Http\HeaderBag;

/**
 * Trait implementing functionality common to requests and responses.
 */
trait MessageTrait
{
    /** @var HeaderBag Map of all registered headers, as original name => array of values */
    protected $headers;

    /** @var string */
    protected $version = '1.1';

    protected $body;
    
    /**
     * @param int                                  $status  Status code
     * @param array                                $headers Response headers
     * @param string|null|resource                 $body    Response body
     * @param string                               $version Protocol version
     * @param string|null                          $reason  Reason phrase (when empty a default will be used based on the status code)
     */
    public function __construct(
        $body = null,
        array $headers = [],
        $version = '1.1'
    ) {
        if ($body !== '' && $body !== null) {
            $this->body = $body;
        }
        $this->headers = new HeaderBag($headers);
        $this->setProtocolVersion($version);
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
            $this->headers."\r\n".
            $this->body;
    }
    public function getProtocolVersion()
    {
        return $this->version;
    }
    /**
     * Sets the HTTP protocol version (1.0 or 1.1).
     *
     * @return $this
     *
     * @final
     */
    public function setProtocolVersion(string $version)
    {
        $this->version = $version;

        return $this;
    }

    public function withProtocolVersion($version)
    {
        if ($this->version === $version) {
            return $this;
        }

        $new = clone $this;
        $new->version = $version;
        return $new;
    }

    public function getHeaders()
    {
        return $this->headers->toArray();
    }

    public function hasHeader($header)
    {
        return $this->headers->has($header);
    }

    public function getHeader($header)
    {
        return $this->headers->get($header,array(),false);
    }

    public function getHeaderLine($header)
    {
        return implode(', ', $this->getHeader($header));
    }

    public function withHeader($header, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $new = clone $this;
        $new->headers->remove($header);
        $new->headers->set($header, $value);
        
        return $new;
    }

    public function withAddedHeader($header, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $new = clone $this;
        $new->headers->set($header, $value);
        return $new;
    }

    public function withoutHeader($header)
    {
        if (!$this->headers->has($header)){
            return $this;
        }
        
        $normalized = strtolower($header);
        $new = clone $this;
        $new->headers->remove($header);
        return $new;
    }

    public function getBody()
    {
        return $this->body;
    }
    public function getContent()
    {
        return $this->body;
    }

    public function withBody($body)
    {
        if ($body === $this->body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function setHeaders($headers)
    {
        $this->headers = new HeaderBag($headers);
    }

    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @param string[] $values Header values
     *
     * @return string[] Trimmed header values
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function trimHeaderValues(array $values)
    {
        return array_map(function ($value) {
            return trim($value, " \t");
        }, $values);
    }
}