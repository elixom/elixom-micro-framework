<?php

namespace Compago\Http;

/**
 * File represents a file for an HTTP response delivering a file.
 *
 * @author Shane Edwards
 */

class File extends \SplFileInfo
{
    public function getMimeType()
    {
        $r3 = substr($this->getPathname(), -3);
        $r4 = substr($this->getPathname(), -4);
        if ($r3 == '.js') return 'application/javascript';
        if ($r4 == '.css') return 'text/css';
        if (function_exists('finfo_open')) {

            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $this->getPathname());
            finfo_close($finfo);
            //__Er('finfo_open', $mimetype);
            return $mimetype;
        }elseif (function_exists('mime_content_type')) {
            //__Er('mime_content_type', $this->getPathname(), mime_content_type($this->getPathname()));
            return mime_content_type($this->getPathname());
        } 
        return 'application/octet-stream';
    }
}
