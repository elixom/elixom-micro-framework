<?php
/**
 * @author Edwards
 * @copyright 2020
 */
namespace Compago\Database\Core;

abstract class Clause{
    abstract function toString();
    public function __toString() {
        return $this->toString();
    }
}