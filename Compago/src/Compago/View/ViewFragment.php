<?php
namespace Compago\View;

use Compago\Contracts\Arrayable;
use Compago\Exceptions\FatalThrowableError;

/**
 * The rendered value is what is return by php include
*/
class ViewFragment extends View{
    
    protected function getContents()
    {
        if ($this->path && file_exists($this->path)){
            return $this->evaluatePhpPath($this->path, $this->data);
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
            $result = (function() use ($__path, $__data){
                extract($__data, EXTR_SKIP);
                return include( $__path);
            })();
        } catch (Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e), $obLevel);
        }
        $ob = ltrim(ob_get_clean());
        if (empty($result)){
            return $ob;
        }
        return $result;
    }
}
