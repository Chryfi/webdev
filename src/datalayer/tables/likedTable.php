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

    /**
     * @return array|null array of beitrag ids
     */
    public function orderByLikesDesc(?int $limit, int $offset) : ?array {
        $limitQuery = isset($limit) ? "LIMIT $limit OFFSET $offset" : "";
        $stmt = $this->db->prepare("SELECT beitrag_id, COUNT(*) as count FROM liket GROUP BY beitrag_id ORDER BY count DESC $limitQuery");
        $result = $stmt->execute();

        if (!$result) return null;

        $ids = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ids[] = $row["beitrag_id"];
        }

        return $ids;
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

    public function removeLikesFromBeitrag(int $beitragID) : bool {
        $stmt = $this->db->prepare("DELETE FROM liket WHERE beitrag_id = :beitrag_id");
        $stmt->bindValue("beitrag_id", $beitragID);

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