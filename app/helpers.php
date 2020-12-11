<?php

function now(){
    return date('Y-m-d H:i:s');
}

/**
 * Return an SQLMapper if the table is exist on the database.
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