<?php

require_once(__DIR__.'/../vendor/autoload.php');

require_once(__DIR__.'/../app/helpers.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

$f3 = Base::instance();

$f3->config(__DIR__.'/../config/config.ini', true);

$f3->config(__DIR__.'/../config/routes.ini');

require_once(__DIR__.'/../config/services.php');

$f3->run();