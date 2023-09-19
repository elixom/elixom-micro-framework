<?php

namespace Compago\Database\SQL;

class Insert extends Dml{
    protected $type = 'INSERT';
    public function __construct($table) {
        if(func_num_args()){
            //addd from clause
            parent::add('TABLE',func_get_arg(0));
        }
    }
}