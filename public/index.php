<?php

require_once(__DIR__.'/../vendor/autoload.php');

$f3 = Base::instance();

$f3->config(__DIR__.'/../config/config.ini');

$f3->config(__DIR__.'/../config/routes.ini');
$f3->run();