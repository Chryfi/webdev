<?php
require_once (BASE_PATH."/src/datalayer/tables/table.php");

class User
{
    private ?int $id = null;
    private string $password;
    private string $email;
    private string $username;
    private string $firstname;
    private string $surname;
    private string $birthday;

    private function __construct()
    {
    }

    /* factory methods */
    public static function createNecessary(string $username, string $password,
                                           string $email, string $firstname, string $surname,
                                           string $birthday) : User
    {
        $user = new User();
        $user->password = $password;
        $user->email = $email;
        $user->username = $username;
        $user->firstname = $firstname;
        $user->surname = $surname;
        $user->birthday = $birthday;

        return $user;
    }

    public static function createFull(?int $id, string $username, string $password,
                                      string $email, string $firstname, string $surname,
                                      string $birthday) : User
    {
        $user = new User();
        $user->id = $id;
        $user->password = $password;
        $user->email = $email;
        $user->username = $username;
        $user->firstname = $firstname;
        $user->surname = $surname;
        $user->birthday = $birthday;

        return $user;
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

class UserTable extends Table
{
    /**
     * @param User $user
     * @return bool
     * @throws PDOException
     */
    public function insertUser(User $user) : bool
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

        if ($result) $user->setId($this->db->getLastInsertedId());

        return $result;
    }

    /**
     * @param int $id
     * @return User|null
     * @throws PDOException
     */
    public function getById(int $id) : ?User {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->bindValue("id", $id);
        $result = $stmt->execute();

        if (!$result) return null;

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->createUser($row);
    }

    /**
     * @param string $username
     * @return User|null
     * @throws PDOException
     */
    public function getByUserName(string $username) : ?User {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE LOWER(username) = :username");
        $stmt->bindValue("username", strtolower($username));
        $result = $stmt->execute();

        if (!$result) return null;

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->createUser($row);
    }

    /**
     * @param string $email
     * @return User|null
     * @throws PDOException
     */
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
        return User::createFull($row["id"], $row["username"], $row["passwort"],
            $row["email"], $row["vorname"], $row["nachname"], $row["geburtstag"]);
    }
}
?>