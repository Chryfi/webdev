<?php
require_once (BASE_PATH."/src/datalayer/tables/table.php");
require_once(BASE_PATH . "/src/datalayer/tables/likedTable.php");

class Beitrag
{
    private ?int $id = null;
    private int $views;
    private string $title;
    private ?int $datetime = null;
    private string $teaser;
    private string $content;
    private int $userId;
    private string $imageName;

    private function __construct()
    { }

    /* Factory methods */
    public static function createFull(int $id, int $views, string $title, int $datetime,
                                      string $teaser, string $content, int $userId, string $imageName) : Beitrag {
        $beitrag = new Beitrag();
        $beitrag->id = $id;
        $beitrag->views = $views;
        $beitrag->title = $title;
        $beitrag->datetime = $datetime;
        $beitrag->teaser = $teaser;
        $beitrag->content = $content;
        $beitrag->userId = $userId;
        $beitrag->imageName = $imageName;

        return $beitrag;
    }

    /**
     * Useful for insertion into database
     */
    public static function createNecessary(string $title, string $teaser, string $content, int $userId, string $imageName) : Beitrag {
        $beitrag = new Beitrag();
        $beitrag->views = 0;
        $beitrag->title = $title;
        $beitrag->teaser = $teaser;
        $beitrag->content = $content;
        $beitrag->userId = $userId;
        $beitrag->imageName = $imageName;

        return $beitrag;
    }

    /**
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     */
    public function setImageName(string $imageName): void
    {
        $this->imageName = $imageName;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getDateTime(): ?int
    {
        return $this->datetime;
    }

    /**
     * @param int $datetime
     */
    public function setDateTime(int $datetime): void
    {
        $this->datetime = $datetime;
    }

    /**
     * @return string
     */
    public function getTeaser(): string
    {
        return $this->teaser;
    }

    /**
     * @param string $teaser
     */
    public function setTeaser(string $teaser): void
    {
        $this->teaser = $teaser;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}



class BeitragTable extends Table {
    /**
     * @param Beitrag $beitrag
     * @return bool
     * @throws PDOException
     */
    public function insertBeitrag(Beitrag $beitrag) : bool {
        $stmt = $this->db->prepare("INSERT INTO beitrag (titel, datum, teaser, beitrag, user_id, image_name)
                                          VALUES (:titel, :datum, :teaser, :beitrag, :user_id, :image_name)");
        $timestamp = time();

        $stmt->bindValue("titel", $beitrag->getTitle());
        $stmt->bindValue("datum", $timestamp);
        $stmt->bindValue("teaser", $beitrag->getTeaser());
        $stmt->bindValue("beitrag", $beitrag->getContent());
        $stmt->bindValue("user_id", $beitrag->getUserId());
        $stmt->bindValue("image_name", $beitrag->getImageName());

        $result = $stmt->execute();

        if ($result) {
            $beitrag->setId($this->db->getLastInsertedId());
            $beitrag->setDateTime($timestamp);
        }

        return $result;
    }

    public function delete(Beitrag $beitrag) : bool {
        if ($beitrag->getId() == null) return false;

        $stmt = $this->db->prepare("DELETE FROM beitrag WHERE id = :id");
        $stmt->bindValue("id", $beitrag->getId());

        return $stmt->execute();
    }

    public function updateBeitrag(Beitrag $beitrag) : bool {
        if ($beitrag->getId() == null) return false;

        $stmt = $this->db->prepare("UPDATE beitrag SET titel = :titel, datum = :datum, teaser = :teaser,
                                          beitrag = :beitrag, image_name = :image_name WHERE id = :id");
        $timestamp = time();

        $stmt->bindValue("titel", $beitrag->getTitle());
        $stmt->bindValue("datum", $timestamp);
        $stmt->bindValue("teaser", $beitrag->getTeaser());
        $stmt->bindValue("beitrag", $beitrag->getContent());
        $stmt->bindValue("image_name", $beitrag->getImageName());
        $stmt->bindValue("id", $beitrag->getId());

        $result = $stmt->execute();

        if ($result) {
            $beitrag->setDateTime($timestamp);
        }

        return $result;
    }

    public function getBeitrag(int $id) : ?Beitrag {
        $stmt = $this->db->prepare("SELECT * FROM beitrag WHERE id = :id");
        $stmt->bindValue("id", $id);
        $result = $stmt->execute();

        if (!$result) return null;

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->createBeitrag($row);
    }

    /**
     * Increments the counter of views.
     * @param int $beitrag_id
     * @return bool
     */
    public function addView(int $beitrag_id) : bool {
        $beitrag = $this->getBeitrag($beitrag_id);

        if (!$beitrag) return false;

        $stmt = $this->db->prepare("UPDATE beitrag SET aufrufzahlen = :aufrufzahlen WHERE id = :id");
        $stmt->bindValue("aufrufzahlen", $beitrag->getViews() + 1);
        $stmt->bindValue("id", $beitrag->getId());

        return $stmt->execute();
    }

    private function createBeitrag(mixed $row) : Beitrag {
        return Beitrag::createFull($row["id"], $row["aufrufzahlen"], $row["titel"], $row["datum"],
            $row["teaser"], $row["beitrag"], $row["user_id"], $row["image_name"]);
    }
}
?>