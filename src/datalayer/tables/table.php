<?php
require_once(BASE_PATH . "/src/datalayer/database.php");

enum DatabaseOperator
{
    case AND;
    case OR;
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