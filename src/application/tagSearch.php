<?php
header('Content-Type: application/json');

require_once (BASE_PATH."/src/datalayer/tables/kategorie.php");

if (isset($_POST["tag-search"]) && $_POST["tag-search"] != "") {
    $kategorieTable = new KategorieTable(getKatzenBlogDatabase());
    $result = $kategorieTable->searchLikeTag($_POST["tag-search"]) ?? array();

    echo json_encode($result);
}
else {
    echo json_encode(array());
}
?>