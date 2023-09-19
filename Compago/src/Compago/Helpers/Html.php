<?php
if (! class_exists('HTML')) {
    class HTML extends \Compago\Html\HtmlUtils{}
}
if (! function_exists('html')) {
    function html($element, array $props = [])
    {
        return \Compago\Html\HtmlUtils::create($element, $props);
    }
}
