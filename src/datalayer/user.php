<?php
require_once (BASE_PATH."/src/datalayer/database.php");

class User
{
    private ?int $id;
    private string $password;
    private string $email;
    private string $username;
    private string $firstname;
    private string $surname;
    private string $birthday;

    public function __construct(?int $id, string $username, string $password,
                                string $email, string $firstname, string $surname,
                                string $birthday)
    {
        $this->id = $id;
        $this->password = $password;
        $this->email = $email;
        $this->username = $username;
        $this->firstname = $firstname;
        $this->surname = $surname;
        $this->birthday = $birthday;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function getBirthday(): string
    {
        return $this->birthday;
    }

    public function setBirthday(string $birthday): void
    {
        $this->birthday = $birthday;
    }
}

class UserTable
{
    private Database $db;

    function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function insertUser(User $user)
    {
        $stmt = $this->db->prepare("INSERT INTO user (passwort, email, username, nachname, vorname, geburtstag)
                                          VALUES (:passwort, :email, :username, :nachname, :vorname, :geburtstag)");

        $stmt->bindValue("passwort", $user->getPassword());
        $stmt->bindValue("email", $user->getEmail());
        $stmt->bindValue("username", $user->getUsername());
        $stmt->bindValue("nachname", $user->getSurname());
        $stmt->bindValue("vorname", $user->getFirstname());
        $stmt->bindValue("geburtstag", $user->getBirthday());

        $result = $stmt->execute();

        $user->setId($this->db->getLastInsertedId());

        return $result;
    }

    public function getByUserName(string $username) : ?User {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->bindValue("username", $username);
        $result = $stmt->execute();

        if (!$result) return null;

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->createUser($row);
    }

    public function getByEmail(string $email) : ?User {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->bindValue("email", $email);
        $result = $stmt->execute();

        if (!$result) return null;

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->createUser($row);
    }

    private function createUser(mixed $row) : User {
        return new User($row["id"], $row["username"], $row["passwort"],
            $row["email"], $row["vorname"], $row["nachname"], $row["geburtstag"]);
    }
}
?>