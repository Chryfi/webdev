<?php
require_once (BASE_PATH."/src/datalayer/tables/table.php");

class LikedTable extends Table {
    /**
     * @param int $beitragID
     * @return int|null null when the fetching failed.
     * @throws PDOException
     */
    public function countLikesBeitrag(int $beitragID) : ?int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM liket WHERE beitrag_id = :beitrag_id");
        $stmt->bindValue("beitrag_id", $beitragID);
        $result = $stmt->execute();

        if (!$result) return null;

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $row["COUNT(*)"];
    }

    public function insertLike(int $beitragID, int $userID) : bool {
        $stmt = $this->db->prepare("INSERT INTO liket (beitrag_id, user_id) VALUES (:beitrag_id, :user_id)");
        $stmt->bindValue("beitrag_id", $beitragID);
        $stmt->bindValue("user_id", $userID);

        return $stmt->execute();
    }

    public function removeLike(int $beitragID, int $userID) : bool {
        $stmt = $this->db->prepare("DELETE FROM liket WHERE beitrag_id = :beitrag_id AND user_id = :user_id");
        $stmt->bindValue("beitrag_id", $beitragID);
        $stmt->bindValue("user_id", $userID);

        return $stmt->execute();
    }

    public function isLikePresent(int $beitragID, int $userID) : bool {
        $stmt = $this->db->prepare("SELECT * FROM liket WHERE beitrag_id = :beitrag_id AND user_id = :user_id");
        $stmt->bindValue("beitrag_id", $beitragID);
        $stmt->bindValue("user_id", $userID);

        $result = $stmt->execute();

        if (!$result) return false;

        if (!$stmt->fetch()) return false;

        return true;
    }
}
?>