<?php
namespace Compago\View;

class NotFoundView extends \Compago\View\View{
    public function render(callable $callback = null){
        $contents = '';
        $response = isset($callback) ? call_user_func($callback, $this, $contents) : null;
        return $contents;
    }
    protected function getContents()
    {
        return '';
    }
}
