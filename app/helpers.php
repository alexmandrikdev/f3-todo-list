<?php

function now(){
    return date('Y-m-d H:i:s');
}

/**
 * Return an SQLMapper if the table is exist on the database
 * 
 * @param string $table 
 * @return Mapper 
 */
function createSQLMapper(string $table): DB\SQL\Mapper
{
    $f3 = Base::instance();

    try {
        return new DB\SQL\Mapper($f3->DB, $table);
    } catch (\PDOException $exception) {
        if($exception->getCode() === '42S02'){
            $f3->error(422, "Unknown table: $table in the database");
        }
        $f3->error(500, $exception);
    }
}

/**
 * Send an error if $table doesn't exist in $mapper
 * 
 * @param Mapper $mapper 
 * @param string $column 
 * @return void 
 */
function checkIfColumnExistsInMapper(\DB\SQL\Mapper $mapper, string $column): void
{
    $f3 = Base::instance();

    if (!$mapper->exists($column)) {
        $f3->error(422, "Unknown column ($column) in " . $mapper->table() . " table");
    }
}