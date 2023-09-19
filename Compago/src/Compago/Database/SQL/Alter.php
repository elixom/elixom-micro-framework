<?php

namespace Compago\Database\SQL;

class Alter extends Ddl{
    protected $type = 'ALTER';
    public function __construct($table=null) {
        parent::__construct('ALTER');
        if (func_num_args()){
            $this->table(func_get_arg(0));
        }
    }
}