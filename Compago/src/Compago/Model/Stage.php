<?php

namespace Compago\Model;

abstract class Stage {
	protected static $dir;
	protected static $ns;
	//abstract static function getDir();
	//abstract static function getNs();
	private static function getClassName($modelName,$className=''){
		if (!$className){
			$className = $modelName;
		}
		if (empty(static::$ns)){
			throw new \Exception('$ns property of Model must be defined.');
		}
		return static::$ns . '\\'. $modelName . '\\' .$className;
	}
    public static function __callStatic($name, $arguments){
        $l = strtolower(substr($name,-5));
        if ($l =='model'){

			if (empty(static::$dir)){
				throw new \Exception('$dir property of Model must be defined.');
			}
            $modelName = substr($name,0,-5);
            $fn = static::$dir . DIRECTORY_SEPARATOR . $modelName. DIRECTORY_SEPARATOR. $modelName.'.php';
            if (file_exists($fn)){
            	include_once($fn);
            }
            $className = static::getClassName($modelName);
            return new $className();
        }
    }
}