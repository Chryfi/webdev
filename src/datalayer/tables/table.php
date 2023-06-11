<?php
require_once(BASE_PATH . "/src/datalayer/database.php");

/* I miss my nice enums from java :*( */
abstract class DatabaseOperator
{
    const AND = "AND";
    const OR = "OR";
}

abstract class Table {
    protected Database $db;

    public function __construct(Database $db)
    {
            $this->db = $db;
    }

    public function getDatabase(): Database {
        return $this->db;
    }
}