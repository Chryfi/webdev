<?php
require_once (BASE_PATH."/src/datalayer/tables/likedTable.php");
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/utils/displayBeitrag.php");
require_once (BASE_PATH."/src/utils/paging.php");

/* quick access for output */
$newPostsHTML = '';
$currentPage = getCurrentPage();
$limit = 5;
$limitPaging = 10;
$countOffset = 0;

$db = getKatzenBlogDatabase();
$beitragTable = new BeitragTable($db);
$pagingRangeTest = getPagingRange(PHP_INT_MAX, $limit, $limitPaging);
/* Only count the current range of values - don't count the entire table */
$newPosts = $beitragTable->orderByDatumDesc(($pagingRangeTest[1] - $pagingRangeTest[0] + 1) * $limit,
    ($pagingRangeTest[0] - 1) * $limit);
/* offset for the paging, as the paging requires the total search count, but we only have the current range */
$countOffset += ($pagingRangeTest[0] - 1) * $limit;

/* the posts contain the range from minimum to maximum page, but we only want to output current page */
if ($newPosts) {
    for ($i = ($currentPage - $pagingRangeTest[0]) * $limit;
         $i - ($currentPage - $pagingRangeTest[0]) * $limit < $limit;
         $i++)
    {
        if ($i >= count($newPosts)) break;

        $newPostsHTML .= getBeitragSpoilerHTML($newPosts[$i], false, null);
    }
}

if ($newPostsHTML == "") {
    $newPostsHTML = '<p class="lead text-center">Keine Daten.</p>';
}

$db->disconnect();

?>

<div class="page-wrapper">
    <header class="container-sm">
        <div class="header-text">
            <h1>Neue Beiträge</h1>
            <p class="lead">Erkunde neue Beiträge von Katzen.</p>
        </div>
    </header>
    <main class="container-sm blog-spoiler-list">
        <?php echo $newPostsHTML; ?>
        <?php echo getPagingHTML(count($newPosts) + $countOffset, $limit, $limitPaging, array(), "neuePosts"); ?>
    </main>
</div>