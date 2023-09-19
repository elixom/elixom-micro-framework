<?php

namespace Compago\Contracts;


interface Responsable
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Compago\Http\Request  $request
     * @return \Compago\Http\Response
     */
    public function toResponse($request);
}
