<?php

require_once (BASE_PATH."/src/utils/credentials.php");

/**
 * @return Database connected database.
 * @throws PDOException when the connection failed
 */
function getKatzenBlogDatabase(): Database {
    $db = new Database("katzenblog", "localhost", "mysql");
    $db->connect(Credentials::getDatabaseUser(), Credentials::getDatabasePassword());

    return $db;
}


class Database {
    private string $databaseName;
    /**
     * Adress as "ip:port"
     */
    private string $address;
    private string $service;

    private ?string $username;
    private ?string $password;
    private ?PDO $connection;

    function __construct(string $databaseName, string $address, string $service) {
        $this->databaseName = $databaseName;
        $this->address = $address;
        $this->service = $service;
    }

    public function getDatabaseName(): string {
        return $this->databaseName;
    }

    public function getAddress(): string {
        return $this->address;
    }

    /**
     * @throws PDOException
     */
    public function connect(string $user, string $password): void {
        if (!isset($user) || !isset($password)) return;

        $this->username = $user;
        $this->$password = $password;
        $this->connection = new PDO($this->service.":host=".$this->address.";dbname=".$this->databaseName, $user, $password);
    }

    public function disconnect(): void {
        $this->connection = null;
    }

    public function isConnected(): bool {
        return $this->connection !== null;
    }

    /**
     * @throws PDOException
     */
    public function prepare(string $query): PDOStatement {
        if (!$this->isConnected()) {
            $this->connect($this->username, $this->password);
        }

        return $this->connection->prepare($query);
    }

    public function getLastInsertedId() : ?int {
        if (!$this->isConnected()) return null;

        return $this->connection->lastInsertId();
    }
}
?>