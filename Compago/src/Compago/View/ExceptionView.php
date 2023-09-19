<?php
namespace Compago\View;

class ExceptionView extends \Exception{
    /**
     * Get the evaluated contents of the view.
     *
     * @return string
     */
    protected function getContents()
    {
        return $this->getMessage();
    }
    public function render(callable $callback = null){
        $contents = $this->getMessage();
        $response = isset($callback) ? call_user_func($callback, $this, $contents) : null;
        return $contents;
    }
    public function with($key, $value = null){
        
    }
}
