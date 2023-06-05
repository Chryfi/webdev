<?php
require_once (BASE_PATH."/src/utils/tags.php");
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/datalayer/tables/likedTable.php");
require_once (BASE_PATH."/src/application/sessionFunctions.php");
require_once (BASE_PATH."/src/utils/tags.php");
require_once (BASE_PATH."/src/application/displayBeitrag.php");

/* quick access for output */
$searchText = $_GET["text-search"] ?? "";
$searchTitle = $_GET["title-search"] ?? "";
$tags = $_GET["tags"] ?? [];
$beitragResults = array();
$currentSearchPage = isset($_GET["page"]) && $_GET["page"] > 0 ? $_GET["page"] : 1;
$countSearchResults = 0;
$limit = 5;
$limitPaging = 10;

if ($searchText != "" || $searchTitle != "" || count($tags) > 0) {
    $db = getKatzenBlogDatabase();
    $beitragTable = new BeitragTable($db);

    $textSearchComponents = [$searchText];//explode(" ", $searchText);
    $titleSearchComponents = [$searchTitle];

    $beitragResults = $beitragTable->searchBeitragLike($titleSearchComponents, DatabaseOperator::AND,
        $textSearchComponents, DatabaseOperator::AND,
        $tags, DatabaseOperator::OR, $limit, ($currentSearchPage - 1) * $limit) ?? array();

    //TODO this sus paging
    $allResults = $beitragTable->searchBeitragLike($titleSearchComponents, DatabaseOperator::AND,
        $textSearchComponents, DatabaseOperator::AND,
        $tags, DatabaseOperator::OR, $limit * $limitPaging, ($currentSearchPage - 1) * $limit) ?? array();

    $countSearchResults = count($allResults);

    $db->disconnect();
}


//TODO paging only show range of 10 pages centered at current page.
function outputPaging(int $countSearchResults, int $limit, int $currentPage, int $limitPaging) {
    $getCopy = $_GET;

    unset($getCopy["p"]);
    unset($getCopy["page"]);
    $countSearchResults += ($currentPage - 1) * $limit;

    echo '<div class="paging-row">';
    $i = max(1, $currentPage - $limitPaging / 2);
    while ($i <= ceil($countSearchResults / $limit)) {
        if ($currentPage == $i) {
            echo '<a class="active">'.$i.'</a>';
        } else {
            echo '<a href="katzegorien?page='.$i.'&'.http_build_query($getCopy).'">'.$i.'</a>';
        }

        if ($i < ceil($countSearchResults / $limit)) {
            echo '<pre style="display: inline-block">  |  </pre>';
        }

        $i++;
    }
    echo '</div>';
}


?>
<div class="page-wrapper">
    <header>
        <div class="header-text">
            <h1>Suche</h1>
        </div>
    </header>
    <main class="container-sm">
        <div class="search-katzegorien-container">
            <form class="search-form" method="GET">
                <input type="text" class="input" name="title-search" placeholder="Titelsuche" value="<?php echo $searchTitle; ?>">
                <input type="text" class="input" name="text-search" placeholder="Freitextsuche" value="<?php echo $searchText; ?>">
                <input type="text" class="input" id="tag-search-input" autocomplete="off" placeholder="Suche nach Tags">
                <div class="blog-info-container row align-items-center justify-content-space-between tag-list-container" id="tag-container">
                    <i class="col-auto fa-solid fa-tags"></i>
                    <div class="col">
                        <div class="row tag-list" id="tag-list">
                            <?php outputTagList($tags); ?>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center submit-row">
                    <div class="col-sm-4 col-md-3 col-xl-2">
                        <button type="submit" class="w-100 button button-primary button-accent">Suche</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="search-katzegorien-result blog-spoiler-list">
            <?php
                foreach ($beitragResults as $beitrag) {
                    echo getBeitragHTML($beitrag, true);
                }
            ?>
        </div>
        <?php outputPaging($countSearchResults, $limit, $currentSearchPage, $limitPaging); ?>
    </main>
</div>
<script>
    let tagList = document.getElementById("tag-list");
    let tagSearchInput = document.getElementById("tag-search-input");

    let tagSearchHandler = new TagSearch(tagSearchInput, tagList);
</script>