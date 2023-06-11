<?php
require_once (BASE_PATH."/src/datalayer/tables/likedTable.php");
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/utils/displayBeitrag.php");

/* quick access for output */
$forYouBeitragHTML = '';

$db = getKatzenBlogDatabase();
$likedTable = new LikedTable($db);
$beitragTable = new BeitragTable($db);
$ids = $likedTable->orderByLikesDesc(5, 0);

if ($ids) {
    foreach ($ids as $id) {
        $beitrag = $beitragTable->getBeitragRelations($id);

        if ($beitrag) {
            $forYouBeitragHTML .= getBeitragSpoilerHTML($beitrag, false, null);
        }
    }
}

if ($forYouBeitragHTML == "") {
    $forYouBeitragHTML = '<p class="lead text-center">Keine Daten.</p>';
}

$db->disconnect();

?>

<div class="page-wrapper">
    <header class="container-sm">
        <div class="header-text">
            <h1>For you page</h1>
            <p class="lead">Dinge die dich interessieren könnten.</p>
        </div>
    </header>
    <main class="container-sm blog-spoiler-list">
        <?php echo $forYouBeitragHTML; ?>
    </main>
</div>