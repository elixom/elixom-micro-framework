<?php
namespace Compago\Http;

interface ResponseInterface extends MessageInterface{
    public function setStatus($code, $reasonPhrase = '');
    public function setBody($body);
    public function setHeaders($headers);
    public function sendHeaders();
    public function sendBody();
    public function send();
}