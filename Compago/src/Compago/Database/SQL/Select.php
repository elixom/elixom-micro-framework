<?php

namespace Compago\Database\SQL;

class Select extends Dml{
    protected $type = 'SELECT';
    public function __construct($table) {
        if(func_num_args()){
            //addd from clause
            $this->add('TABLE',func_get_arg(0));
        }
    }
}