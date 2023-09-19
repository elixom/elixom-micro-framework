<?php
/**
 * @author Edwards
 * @copyright 2017
 */
namespace Elix\DB;
function __deprecated($fx,$explain=''){
    trigger_error("This function $fx() is deprecated. $explain");
}

class DBO extends \Compago\Database\DatabaseInstance
{   
}
class dbException extends \Compago\Database\ConnectionError
{   
}
