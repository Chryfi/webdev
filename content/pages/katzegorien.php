<?php
require_once (BASE_PATH."/src/utils/tags.php");
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/datalayer/tables/likedTable.php");
require_once (BASE_PATH."/src/application/sessionFunctions.php");
require_once (BASE_PATH."/src/utils/tags.php");
require_once (BASE_PATH."/src/application/displayBeitrag.php");

/* quick access for output */
$counterHtml = "";
$searchText = $_GET["text-search"] ?? "";
$searchTitle = $_GET["title-search"] ?? "";
$tags = $_GET["tags"] ?? [];
$beitragResults = array();
$currentSearchPage = isset($_GET["page"]) && $_GET["page"] > 0 ? $_GET["page"] : 1;
$countSearchResults = 0;
$limit = 5;
$limitPaging = 10;
$getParameter = null;
$isAdvancedSearch = false;

if ($searchText != "" || $searchTitle != "" || count($tags) > 0) {
    if ($searchText != "" || count($tags) > 0) {
        $isAdvancedSearch = true;
    }

    $db = getKatzenBlogDatabase();
    $beitragTable = new BeitragTable($db);

    $textSearchComponents = explode(" ", $searchText);
    $titleSearchComponents = explode(" ", $searchTitle);

    $beitragResults = $beitragTable->searchBeitragLike($titleSearchComponents, DatabaseOperator::AND,
        $textSearchComponents, DatabaseOperator::AND,
        $tags, DatabaseOperator::AND, $limit, ($currentSearchPage - 1) * $limit) ?? array();

    $countSearchResults = $beitragTable->countBeitragLike($titleSearchComponents, DatabaseOperator::AND,
        $textSearchComponents, DatabaseOperator::AND,
        $tags, DatabaseOperator::AND, null, 0);

    $counterHtml = getSearchCountHTML($countSearchResults, $currentSearchPage);

    $getCopy = $_GET;
    unset($getCopy["p"]);
    $getParameter = http_build_query($getCopy);

    $db->disconnect();
}

function getSearchCountHTML(int $totalCount, int $currentPage) : string {
    $ergebnisString = $totalCount > 1 ? " Ergebnisse" : " Ergebnis";
    $pageString = $currentPage > 1 ? "Seite ".$currentPage." " : "";
    $insgesamtString = $currentPage > 1 ? "von insgesamt " : "Insgesamt ";

    return '<p class="lead text-center">'.$pageString.$insgesamtString.$totalCount.$ergebnisString.'</p>';
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
            <form class="gap-y-1" method="GET">
                <input type="text" class="input" name="title-search" placeholder="Titelsuche" value="<?php echo $searchTitle; ?>">
                <div>
                    <button class="dropdown-button dropdown-button-classic row align-items-center <?php if (!$isAdvancedSearch) echo 'collapsed'; ?>" data-dropdown-target="extended-search">
                        <i class="col-auto fa-solid fa-magnifying-glass"></i>
                        <div class="col-auto icon-text-right"> Erweiterte Suche</div>
                    </button>
                    <div class="dropdown-collapse <?php if (!$isAdvancedSearch) echo 'collapsed'; ?>" id="extended-search">
                        <div class="dropdown-body gap-y-1">
                            <input type="text" class="input" name="text-search" placeholder="Freitextsuche" value="<?php echo $searchText; ?>">
                            <div>
                                <input type="text" class="input" id="tag-search-input" autocomplete="off" placeholder="Suche nach Tags">
                                <div class="blog-info-container row align-items-center justify-content-space-between tag-list-container" id="tag-container">
                                    <i class="col-auto fa-solid fa-tags"></i>
                                    <div class="col">
                                        <div class="row tag-list" id="tag-list">
                                            <?php outputTagList($tags); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                echo $counterHtml;

                foreach ($beitragResults as $beitrag) {
                    echo getBeitragSpoilerHTML($beitrag, true, $getParameter);
                }
            ?>
        </div>
        <?php
            if ($countSearchResults > 0) {
                $getCopy = $_GET;
                unset($getCopy["p"]);
                echo getPagingHTML($countSearchResults, $limit, $limitPaging, $getCopy);
            }
        ?>
    </main>
</div>
<script>
    let tagList = document.getElementById("tag-list");
    let tagSearchInput = document.getElementById("tag-search-input");

    let tagSearchHandler = new TagSearch(tagSearchInput, tagList);
</script>