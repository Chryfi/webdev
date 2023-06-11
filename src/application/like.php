<?php
header('Content-Type: application/json');

require_once(BASE_PATH . "/src/utils/sessionFunctions.php");
require_once(BASE_PATH . "/src/datalayer/tables/beitrag.php");
require_once(BASE_PATH . "/src/datalayer/tables/likedTable.php");

$result = false;

if (isset($_POST["id"]) && $_POST["id"] != "" && isLoggedin()) {
    if (isset($_POST["add"])) {
        $result = addLike($_POST["id"], getSessionUserId());
    } else if (isset($_POST["remove"])) {
        $result = removeLike($_POST["id"], getSessionUserId());
    }
}
echo json_encode(["status" => $result]);


function addLike(int $beitragID, int $userID) : bool {
    $db = getKatzenBlogDatabase();
    $beitragTable = new BeitragTable($db);
    $likedTable = new LikedTable($db);

    $result = false;
    if (!$likedTable->isLikePresent($beitragID, $userID)
        && $beitragTable->getBeitrag($beitragID) != null) {
        $result = $likedTable->insertLike($beitragID, $userID);
    }

    $db->disconnect();

    return $result;
}


function removeLike(int $beitragID, int $userID) : bool {
    $db = getKatzenBlogDatabase();
    $likedTable = new LikedTable($db);

    $result = false;
    if ($likedTable->isLikePresent($beitragID, $userID)) {
        $result = $likedTable->removeLike($beitragID, $userID);
    }

    $db->disconnect();

    return $result;
}
?>