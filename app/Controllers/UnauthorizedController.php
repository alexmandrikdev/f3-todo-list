<?php

class UnauthorizedController
{
    function __invoke(Base $f3)
    {
        $f3->set('view', 'unauthorized.htm');
        echo Template::instance()->render('layout.htm');
    }
}
