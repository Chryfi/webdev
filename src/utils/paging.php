<?php

function getCurrentPage() : int {
    return isset($_GET["page"]) && $_GET["page"] > 0 ? $_GET["page"] : 1;
}

function getPagingHTML(int $countSearchResults, int $limit, int $limitPaging, ?array $getParameter, string $page) : string {
    unset($getParameter["page"]);
    $currentPage = getCurrentPage();

    $minPage = getPagingRange($countSearchResults, $limit, $limitPaging)[0];
    $maxPage = getPagingRange($countSearchResults, $limit, $limitPaging)[1];
    $getParameterStr = $getParameter ? "&".http_build_query($getParameter) : "";

    $pagingHTML = '<div class="paging-row">';
    $i = $minPage;

    if ($currentPage > 1) {
        $pagingHTML .= '<a href="'.$page.'?page='.($currentPage - 1).$getParameterStr.'">Zurück</a>';
    }

    $pagingHTML .= '<div class="page-links-row">';
    while ($i <= $maxPage) {
        if ($currentPage == $i) {
            $pagingHTML .= '<a class="active">'.$i.'</a>';
        } else {
            $pagingHTML .= '<a href="'.$page.'?page='.$i.$getParameterStr.'">'.$i.'</a>';
        }

        $i++;
    }
    $pagingHTML .= '</div>';

    if ($currentPage < $maxPage) {
        $pagingHTML .= '<a href="'.$page.'?page='.($currentPage + 1).$getParameterStr.'">Weiter</a>';
    }

    $pagingHTML .= '</div>';

    return $pagingHTML;
}


/**
 * @param int $countSearchResults
 * @param int $limit
 * @param int $limitPaging
 * @return array [minPage, maxPage]
 */
function getPagingRange(int $countSearchResults, int $limit, int $limitPaging) : array {
    $currentPage = getCurrentPage();
    $maxCountPages = ceil($countSearchResults / $limit);
    $minPage = min(max($currentPage - floor($limitPaging / 2) + 1, 1), $maxCountPages);
    $maxPage = $minPage + $limitPaging - 1;

    if ($maxPage > $maxCountPages) {
        $minPage = max($minPage - ($maxPage - $maxCountPages), 1);
    }

    $maxPage = min($maxPage, $maxCountPages);

    return [$minPage, $maxPage];
}