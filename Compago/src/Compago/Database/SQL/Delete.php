<?php

namespace Compago\Database\SQL;

class Delete extends Dml{
    protected $type = 'DELETE';
    public function __construct($table) {
        if(func_num_args()){
            //addd from clause
            $this->add('TABLE',func_get_arg(0));
        }
    }
}