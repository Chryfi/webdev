<?php

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
        if ($user === null && $password === null) return;

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
}
?>