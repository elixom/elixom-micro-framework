<?php

namespace Compago\Database\SQL;

class Create extends Ddl{
    protected $type = 'CREATE';
    public function __construct($table=null) {
        parent::__construct('TRUNCATE');
        if (func_num_args()){
            $this->table(func_get_arg(0));
        }
    }
}