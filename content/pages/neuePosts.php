<?php
require_once (BASE_PATH."/src/datalayer/tables/likedTable.php");
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/application/displayBeitrag.php");

/* quick access for output */
$newPostsHTML = '';

$db = getKatzenBlogDatabase();
$beitragTable = new BeitragTable($db);
$newPosts = $beitragTable->orderByDatumDesc(7, 0);

if ($newPosts) {
    foreach ($newPosts as $beitrag) {
        $newPostsHTML .= getBeitragSpoilerHTML($beitrag, false, null);
    }
}

if ($newPostsHTML == "") {
    $newPostsHTML = '<p class="lead">Ein Fehler ist aufgetreten beim Sammeln der Daten.</p>';
}

$db->disconnect();

?>

<div class="page-wrapper">
    <header>
        <div class="header-text">
            <h1>Neue Beiträge</h1>
            <p class="lead">Erkunde neue Beiträge von Katzen.</p>
        </div>
    </header>
    <main class="container-sm blog-spoiler-list">
        <?php echo $newPostsHTML; ?>
    </main>
</div>