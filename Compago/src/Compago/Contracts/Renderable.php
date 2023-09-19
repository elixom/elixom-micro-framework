<?php

namespace Compago\Contracts;

interface Renderable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function render();
}
