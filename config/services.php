<?php

$db = new DB\SQL(
    'mysql:host='.$f3->DB_HOST.';post='.$f3->DB_PORT.';dbname='.$f3->DB_TABLE.';charset=utf8',
    $f3->DB_USERNAME,
    $f3->DB_PASSWORD,
    [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]
);

$f3->set('DB', $db);

/**
 * Initialize flash
 */
$flash = Flash::instance();

$f3->set('FLASH', $flash);
