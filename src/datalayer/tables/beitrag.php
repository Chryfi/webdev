<?php
require_once (BASE_PATH."/src/datalayer/tables/table.php");
require_once(BASE_PATH . "/src/datalayer/tables/likedTable.php");
require_once(BASE_PATH . "/src/datalayer/tables/kategorie.php");

class Beitrag
{
    protected ?int $id = null;
    protected int $views;
    protected string $title;
    protected ?int $datetime = null;
    protected string $teaser;
    protected string $content;
    protected int $userId;
    protected string $imageName;

    protected function __construct(?int $id, int $views, string $title, ?int $datetime,
                                   string $teaser, string $content, int $userId, string $imageName)
    {
        $this->id = $id;
        $this->views = $views;
        $this->title = $title;
        $this->datetime = $datetime;
        $this->teaser = $teaser;
        $this->content = $content;
        $this->userId = $userId;
        $this->imageName = $imageName;
    }

    /* Factory methods */
    public static function createFull(int $id, int $views, string $title, int $datetime,
                                      string $teaser, string $content, int $userId, string $imageName) : Beitrag
    {
        return new Beitrag($id, $views, $title, $datetime, $teaser, $content, $userId, $imageName);
    }

    /**
     * Useful for insertion into database
     */
    public static function createNecessary(string $title, string $teaser, string $content,
                                           int $userId, string $imageName) : Beitrag
    {
        return new Beitrag(null, 0, $title, null, $teaser, $content, $userId, $imageName);
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

/**
 * This contains all database relations of a beitrag too.
 */
class BeitragRelations extends Beitrag {
    private int $likes;
    private array $tags;

    public function __construct(Beitrag $beitrag, array $tags, int $likes)
    {
        parent::__construct($beitrag->id, $beitrag->views, $beitrag->title, $beitrag->datetime,
            $beitrag->teaser, $beitrag->content, $beitrag->userId, $beitrag->imageName);

        $this->tags = $tags;
        $this->likes = $likes;
    }

    /**
     * @return int
     */
    public function getLikes(): int
    {
        return $this->likes;
    }

    /**
     * @param int $likes
     */
    public function setLikes(int $likes): void
    {
        $this->likes = $likes;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
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

    public function getBeitragRelations(int $id) : ?BeitragRelations {
        $beitrag = $this->getBeitrag($id);
        if (!$beitrag) return null;

        $likedTable = new LikedTable($this->db);
        $kategorieTable = new KategorieTable($this->db);
        $likes = $likedTable->countLikesBeitrag($id) ?? 0;
        $tags = $kategorieTable->getTagsByBeitrag($id) ?? array();

        return new BeitragRelations($beitrag, $tags, $likes);
    }

    /**
     * @param int|null $limit optional limit
     * @param int $offset
     * @return array|null array of {@link BeitragRelations} or null if the query failed.
     */
    public function orderByDatumDesc(?int $limit, int $offset) : ?array {
        $limitQuery = isset($limit) ? "LIMIT $limit OFFSET $offset" : "";
        $stmt = $this->db->prepare("SELECT * FROM beitrag ORDER BY datum DESC $limitQuery");
        $result = $stmt->execute();

        if (!$result) return null;

        $searchResults = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $beitragRelation = $this->getBeitragRelations($row["id"]);

            if ($beitragRelation) {
                $searchResults[] = $beitragRelation;
            }
        }

        return $searchResults;
    }

    public function searchBeitragLike(array $title, string $titleOP,
                                      array $text, string $textOP,
                                      array $tags, string $tagsOP,
                                      ?int $limit, int $offset) : ?array {
        return $this->_searchBeitragLike($title, $titleOP, $text, $textOP, $tags, $tagsOP, $limit, $offset, false);
    }

    public function countBeitragLike(array $title, string $titleOP,
                                      array $text, string $textOP,
                                      array $tags, string $tagsOP,
                                      ?int $limit, int $offset) : int {
        return $this->_searchBeitragLike($title, $titleOP, $text, $textOP, $tags, $tagsOP, $limit, $offset, true);
    }

    /**
     * @return array|null array of {@link BeitragRelations} or null if something went wrong.
     */
    private function _searchBeitragLike(array $title, string $titleOP,
                                      array $text, string $textOP,
                                      array $tags, string $tagsOP,
                                      ?int $limit, int $offset, bool $count) : mixed
    {
        $titleSearchQuery = "";
        for ($i = 0; $i < count($title); $i++) {
            if ($titleSearchQuery != "") {
                if ($titleOP == DatabaseOperator::AND) {
                    $titleSearchQuery .= " AND";
                } else if ($titleOP == DatabaseOperator::OR) {
                    $titleSearchQuery .= " OR";
                }
            }

            $titleSearchQuery .= " titel LIKE :titel$i";
        }

        $textSearchQuery = "";
        for ($i = 0; $i < count($text); $i++) {
            if ($textSearchQuery != "") {
                if ($textOP == DatabaseOperator::AND) {
                    $textSearchQuery .= " AND";
                } else if ($textOP == DatabaseOperator::OR) {
                    $textSearchQuery .= " OR";
                }
            }

            $textSearchQuery .= " (beitrag LIKE :beitrag$i OR teaser LIKE :teaser$i)";
        }

        $tagSearchQuery = "";
        for ($i = 0; $i < count($tags); $i++) {
            if ($tagSearchQuery != "") {
                if ($tagsOP == DatabaseOperator::AND) {
                    $tagSearchQuery .= " AND";
                } else if ($tagsOP == DatabaseOperator::OR) {
                    $tagSearchQuery .= " OR";
                }
            }

            $tagSearchQuery .= " kategorie.bezeichnung = :bezeichnung$i";
        }

        $limitQuery = $limit != null ? "LIMIT $limit OFFSET $offset" : "";
        $whereStmt = "";
        $whereStatements = array();
        $whereStatements[] = $titleSearchQuery != "" ? "(".$titleSearchQuery.")" : "";
        $whereStatements[] = $textSearchQuery != "" ? "(".$textSearchQuery.")" : "";
        $whereStatements[] = $tagSearchQuery != "" ? "(".$tagSearchQuery.")" : "";

        foreach ($whereStatements as $where) {
            if ($whereStmt != "" && $where != "") {
                $whereStmt .= " AND";
            }

            if ($where != "") {
                $whereStmt .= " ".$where;
            }
        }

        $selectionThing = $count ? "COUNT(*)" : "*";

        $searchQuery = "SELECT $selectionThing FROM beitrag WHERE $whereStmt $limitQuery";

        //TODO tag suche funktioniert nicht wenn man die tags mit AND verbindet. z.B. HAVING bezeichnung = "test" AND bezeichnung = "test2" funktioniert garnicht.
        //HAVING mit nur einem tag funktioniert auch nicht richtig.
        if ($tagSearchQuery != "") {
            $whereStmt = $whereStmt != "" ? $whereStmt." AND" : "";
            if ($count) {
                $searchQuery = "SELECT COUNT(*) FROM beitrag WHERE beitrag.id IN
                                   (SELECT beitrag.id FROM beitrag, kategorie WHERE $whereStmt beitrag.id = kategorie.beitrag_id GROUP BY beitrag.id $limitQuery)";
            } else {
                $searchQuery = "SELECT * FROM beitrag, kategorie WHERE $whereStmt beitrag.id = kategorie.beitrag_id GROUP BY beitrag.id $limitQuery";
            }
        }

        $stmt = $this->db->prepare($searchQuery);

        for ($i = 0; $i < count($title); $i++) {
            $stmt->bindValue("titel".$i, "%".$title[$i]."%");
        }

        for ($i = 0; $i < count($text); $i++) {
            $stmt->bindValue("beitrag".$i, "%".$text[$i]."%");
            $stmt->bindValue("teaser".$i, "%".$text[$i]."%");
        }

        if ($tagSearchQuery != "") {
            for ($i = 0; $i < count($tags); $i++) {
                $stmt->bindValue("bezeichnung".$i, $tags[$i]);
            }
        }

        $result = $stmt->execute();

        if (!$result) return null;

        if ($count) {
            $row = $stmt->fetch();

            if (!$row) return 0;

            return  $row["COUNT(*)"];
        } else {
            $searchResults = array();
            while ($row = $stmt->fetch()) {
                if ($beitrag = $this->getBeitragRelations($row["id"])) {
                    $searchResults[] = $beitrag;
                }
            }

            return $searchResults;
        }
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