<?php
header('Content-Type: application/json');

require_once (BASE_PATH."/src/utils/sessionFunctions.php");
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/datalayer/tables/kategorie.php");
require_once (BASE_PATH."/src/datalayer/tables/likedTable.php");

$status = false;

if (isset($_POST["delete"]) && $_POST["delete"] != ""
    && is_numeric($_POST["delete"]) && isLoggedin())
{
    $db = getKatzenBlogDatabase();
    $beitragTable = new BeitragTable($db);

    $beitrag = $beitragTable->getBeitrag($_POST["delete"]);

    if ($beitrag && $beitrag->getUserId() == getSessionUserId()) {
        $status = deleteBeitragAndRelations($_POST["delete"]);
    }

    $db->disconnect();
}

echo json_encode(["status" => $status]);


/**
 * Deletes the beitrag and all relations (likes, tags and image files)
 * @param int $beitragID
 * @return bool
 */
function deleteBeitragAndRelations(int $beitragID): bool {
    $db = getKatzenBlogDatabase();
    $beitragTable = new BeitragTable($db);
    $likedTable = new LikedTable($db);
    $kategorieTable = new KategorieTable($db);
    $beitrag = $beitragTable->getBeitrag($beitragID);

    if (!$beitrag) {
        $db->disconnect();
        return false;
    }

    $likedTable->removeLikesFromBeitrag($beitrag->getId());
    $kategorieTable->removeTagsFromBeitrag($beitrag->getId());
    $status = $beitragTable->delete($beitrag);

    unlink($beitrag->getImageName());

    $db->disconnect();

    return $status;
}