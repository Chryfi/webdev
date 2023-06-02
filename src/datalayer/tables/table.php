<?php
require_once(BASE_PATH . "/src/datalayer/database.php");

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