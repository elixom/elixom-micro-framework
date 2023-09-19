<?php

namespace Compago\Model;

abstract class Schema {
	abstract static function migrate();
}