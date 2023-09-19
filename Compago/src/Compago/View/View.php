<?php
namespace Compago\View;

use Exception;
use Throwable;
use Compago\Contracts\Arrayable;
use Compago\Exceptions\FatalThrowableError;

/**
 * The rendered value is what is echo out by the view
*/

class View {
    /**
     * The name of the view.
     *
     * @var string
     */
    protected $view;

    /**
     * The array of view data.
     *
     * @var array
     */
    protected $data;

    /**
     * The path to the view file.
     *
     * @var string
     */
    protected $path;
    
    /**
     * The view factory instance.
     *
     * @var \Compago\App
     */
    protected $factory;
    /**
     * Create a new view instance.
     *
     * @param  \Compago\App  $factory
     * @param  string  $view
     * @param  string  $path
     * @param  mixed  $data
     * @return void
     */
    public function __construct(\Compago\App $app, $view, $path, $data = [])
    {
        $this->view = $view;
        $this->path = $path;
        $this->factory = $app;

        $this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;
    }
    /**
     * Add a piece of data to the view.
     *
     * @param  string|array  $key
     * @param  mixed   $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }
    /**
     * Get the evaluated contents of the view.
     *
     * @return string
     */
    protected function getContents()
    {
        if ($this->path && file_exists($this->path)){
            return $this->evaluatePhpPath($this->path, $this->data);
        }
        return null;
    }
    /**
     * Get the string contents of the view.
     *
     * @param  callable|null  $callback
     * @return string
     *
     * @throws \Throwable
     */
    public function render(callable $callback = null)
    {
        try {
            $contents = $this->getContents();

            $response = isset($callback) ? call_user_func($callback, $this, $contents) : null;

            return ! is_null($response) ? $response : $contents;
        } catch (Exception $e) {
            
            throw $e;
        }
        return null;
    }
    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param  string  $__path
     * @param  array   $__data
     * @return string
     */
    protected function evaluatePhpPath($__path, $__data)
    {
        $obLevel = ob_get_level();
        ob_start();
        
        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            $return = (function() use ($__path, $__data){
                extract($__data, EXTR_SKIP);
                return include( $__path);
            })();
            
            
        } catch (Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e), $obLevel);
        }
        $ob = ltrim(ob_get_clean());
        if (empty($ob)){
            return $return;
        }
        return $ob;
    }
    /**
     * Handle a view exception.
     *
     * @param  \Exception  $e
     * @param  int  $obLevel
     * @return void
     *
     * @throws \Exception
     */
    protected function handleViewException(Exception $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }
}
