<?php

$db = new DB\SQL(
    'mysql:host=localhost;post=3306;dbname=f3-todo-list;charset=utf8',
    $f3->DB_USERNAME,
    $f3->DB_PASSWORD,
    [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]
);

$f3->set('DB', $db);
