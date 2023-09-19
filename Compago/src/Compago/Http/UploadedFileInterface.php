<?php
namespace Compago\Http;

interface UploadedFileInterface{
    public function moveTo($targetPath);
    public function getSize();
    public function getClientMediaType();
}