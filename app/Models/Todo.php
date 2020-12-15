<?php

class Todo extends DB\SQL\Mapper
{
    private $f3;

    function __construct()
    {
        $this->f3 = Base::instance();

        parent::__construct($this->f3->DB, 'todos');
    }

}
