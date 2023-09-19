<?php
namespace Compago\View;

use Compago\Contracts\Arrayable;

/**
 * Get the plain text of a file
*/
class ViewText extends View{
    
    protected function getContents()
    {
        if ($this->path && file_exists($this->path)){
            return file_get_contents($this->path);
        }
        return null;
    }
}
