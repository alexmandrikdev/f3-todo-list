<?php

class Controller 
{
    function afterroute() {
		echo Template::instance()->render('layout.htm');
	}
}