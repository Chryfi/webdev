<?php
require_once (BASE_PATH."/src/datalayer/tables/table.php");

class KategorieTable extends Table
{
    public function insertTag(string $tag, int $beitragId) : bool
    {
        $stmt = $this->db->prepare("INSERT INTO kategorie (beitrag_id, bezeichnung) VALUES (:beitrag_id, :bezeichnung)");

        $stmt->bindValue("beitrag_id", $beitragId);
        $stmt->bindValue("bezeichnung", $tag);

        return $stmt->execute();
    }

    public function removeTagFromBeitrag(string $tag, int $beitragId) : bool
    {
        $stmt = $this->db->prepare("DELETE FROM kategorie WHERE beitrag_id = :beitrag_id AND bezeichnung = :bezeichnung");

        $stmt->bindValue("beitrag_id", $beitragId);
        $stmt->bindValue("bezeichnung", $tag);

        return $stmt->execute();
    }

    public function removeTagsFromBeitrag(int $beitragId) : bool
    {
        $stmt = $this->db->prepare("DELETE FROM kategorie WHERE beitrag_id = :beitrag_id");

        $stmt->bindValue("beitrag_id", $beitragId);

        return $stmt->execute();
    }

    public function getTagsByBeitrag(int $beitrag_id) : ?array {
        $stmt = $this->db->prepare("SELECT * FROM kategorie WHERE beitrag_id = :beitrag_id");
        $stmt->bindValue("beitrag_id", $beitrag_id);
        $result = $stmt->execute();

        if (!$result) return null;

        $tags = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tags[] = $row["bezeichnung"];
        }

        return $tags;
    }

    /**
     * @param string $searchName
     * @return array|null null if the search failed.
     * Otherwise, returns an associative array where the key is the tag name and the value is how often this appeared.
     */
    public function searchLikeTag(string $searchName) : ?array {
        $stmt = $this->db->prepare("SELECT bezeichnung, COUNT(*) FROM kategorie 
                             WHERE bezeichnung LIKE :search GROUP BY bezeichnung");
        $stmt->bindValue("search", "%".$searchName."%");
        $result = $stmt->execute();

        if (!$result) return null;

        $tags = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tags[$row["bezeichnung"]] = $row["COUNT(*)"];
        }

        return $tags;
    }
}
