<?php
namespace Compago\Http;

use JsonSerializable;
use \Compago\Contracts\Arrayable;
use \Compago\Contracts\Jsonable;


class JsonResponse extends Response{
    protected $data;
    protected $callback;

    // Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
    // 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    const DEFAULT_ENCODING_OPTIONS = 15;

    protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;

    /**
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     * @param bool  $json    If the data is already a JSON string
     */
    public function __construct($data = null, int $status = 200, array $headers = array(), bool $json = false)
    {
        parent::__construct('', $status, $headers);

        if (null === $data) {
            $data = new \ArrayObject();
        }

        $json ? $this->setJson($data) : $this->setData($data);
    }
    /**
     * Factory method for chainability.
     *
     * Example:
     *
     *     return JsonResponse::create($data, 200)
     *         ->setSharedMaxAge(300);
     *
     * @param mixed $data    The json response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return static
     */
    public static function create($data = null, $status = 200, $headers = array())
    {
        return new static($data, $status, $headers);
    }

    /**
     * Make easier the creation of JsonResponse from raw json.
     */
    public static function fromJsonString($data = null, $status = 200, $headers = array())
    {
        return new static($data, $status, $headers, true);
    }
    /**
     * Sets the JSONP callback.
     *
     * @param string|null $callback The JSONP callback or null to use none
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When the callback name is not valid
     */
    public function setCallback($callback = null)
    {
        if (null !== $callback) {
            // partially taken from http://www.geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License. See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = array(
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 'for', 'switch', 'while',
                'debugger', 'function', 'this', 'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super',  'const', 'export',
                'import', 'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static', 'null', 'true', 'false',
            );
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part) || \in_array($part, $reserved, true)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $this->callback = $callback;

        return $this->update();
    }
    
    /**
     * Sets the JSONP callback.
     *
     * @param  string|null  $callback
     * @return $this
     */
    public function withCallback($callback = null)
    {
        return $this->setCallback($callback);
    }
        /**
     * Get the json_decoded data from the response.
     *
     * @param  bool  $assoc
     * @param  int  $depth
     * @return mixed
     */
    public function getData($assoc = false, $depth = 512)
    {
        return json_decode($this->data, $assoc, $depth);
    }


    /**
     * Sets a raw string containing a JSON document to be sent.
     *
     * @param string $json
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setJson($json)
    {
        $this->original = $json;
        $this->data = $json;

        return $this->update();
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setData($data = [])
    {
        $this->original = $data;
        try {
            if ($data instanceof Jsonable) {
                $this->data = $data->toJson($this->encodingOptions);
            } elseif ($data instanceof JsonSerializable) {
                $this->data = json_encode($data->jsonSerialize(), $this->encodingOptions);
            } elseif ($data instanceof Arrayable) {
                $this->data = json_encode($data->toArray(), $this->encodingOptions);
            } else {
                $this->data = json_encode($data, $this->encodingOptions);
            }
        } catch (\Exception $e) {
            if ('Exception' === \get_class($e) && 0 === strpos($e->getMessage(), 'Failed calling ')) {
                throw $e->getPrevious() ?: $e;
            }
            throw $e;
        }

        if (! $this->hasValidJson(json_last_error())) {
            throw new \InvalidArgumentException(\json_last_error_msg());
        }

        return $this->update();
    }
    /**
     * Determine if an error occurred during JSON encoding.
     *
     * @param  int  $jsonError
     * @return bool
     */
    protected function hasValidJson($jsonError)
    {
        if ($jsonError === JSON_ERROR_NONE) {
            return true;
        }

        return $this->hasEncodingOption(JSON_PARTIAL_OUTPUT_ON_ERROR) &&
                    in_array($jsonError, [
                        JSON_ERROR_RECURSION,
                        JSON_ERROR_INF_OR_NAN,
                        JSON_ERROR_UNSUPPORTED_TYPE,
                    ]);
    }
    /**
     * Returns options used while encoding data to JSON.
     *
     * @return int
     */
    public function getEncodingOptions()
    {
        return $this->encodingOptions;
    }

    /**
     * Sets options used while encoding data to JSON.
     *
     * @param int $encodingOptions
     *
     * @return $this
     */
    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = (int) $encodingOptions;

        return $this->setData($this->getData());
    }

    /**
     * Determine if a JSON encoding option is set.
     *
     * @param  int  $option
     * @return bool
     */
    public function hasEncodingOption($option)
    {
        return (bool) ($this->encodingOptions & $option);
    }
    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return $this
     */
    protected function update()
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setBody(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }

        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setBody($this->data);
    }
}