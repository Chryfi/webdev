<?php
require_once (BASE_PATH."/src/datalayer/tables/user.php");
require_once (BASE_PATH."/src/datalayer/tables/likedTable.php");
require_once (BASE_PATH."/src/utils/sessionFunctions.php");
require_once (BASE_PATH."/src/utils/tags.php");

function getBeitragSpoilerHTML(BeitragRelations $beitrag, bool $showTags, ?string $getParameter) : string {
    $db = getKatzenBlogDatabase();
    $userTable = new UserTable($db);
    $author = $userTable->getById($beitrag->getUserId());
    $likedTable = new LikedTable($db);
    $userLikedThis = false;

    if (isLoggedin()) {
        $userLikedThis = $likedTable->isLikePresent($beitrag->getId(), getSessionUserId());
    }

    $db->disconnect();

    $likeClass = $userLikedThis ? "active" : "";
    $date = date("d.m.Y", $beitrag->getDateTime());
    $authorUsername = isset($author) ? $author->getUsername() : "No user found";
    $imageLink = str_replace(BASE_PATH, "", $beitrag->getImageName());


    $tagsHTML = "";
    $tagList = getSimpleTagList($beitrag->getTags());

    if ($showTags) {
        $tagsHTML = <<<HTML
        <div class="tag-list-container blog-spoiler-tag-list-container row align-items-center justify-content-space-between">
            <i class="col-auto fa-solid fa-tags"></i>
            <div class="col">
                <div class="row tag-list">
                    {$tagList}
                </div>
            </div>
        </div>
        HTML;
    }

    $getParameter = isset($getParameter) ? "&".$getParameter : "";

    return <<<HTML
<div class="blog-spoiler-container" onclick="window.location.href='/article?id={$beitrag->getId()}{$getParameter}';">
    <div class="container-fluid display-md-none">
        <h2 class="text-center blog-spoiler-title">{$beitrag->getTitle()}</h2>
    </div>
    <div class="row blog-spoiler">
        <div class="col-md-5 col-xl-4 blog-thumbnail-img-container">
            <img class="blog-thumbnail-img" src="{$imageLink}">
            <div class="row margin-y-05 display-md-none">
                <div class="col-md-5">
                    <p>{$authorUsername} | {$date}</p>
                </div>
                <div class="col-md-5">
                    <div class="row stats-row">
                        <div class="col-auto stats-container">
                            <p class="like-counter {$likeClass}">{$beitrag->getLikes()}</p>
                            <i class="fa-solid fa-heart like-button mute-cursor-effects {$likeClass}"></i>
                        </div>
                        <div class="col-auto stats-container">
                            <p>{$beitrag->getViews()}</p><i class="fa-regular fa-eye"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5 col-xl-6 row">
            <div class="blog-spoiler-text-container">
                <div class="read-further-container">
                    <h3 class="read-further-text">WEITER LESEN</h3>
                </div>
                <div class="blog-spoiler-text">
                    <h2 class="blog-spoiler-title display-md-max-none">{$beitrag->getTitle()}</h2>
                    <p>{$beitrag->getTeaser()}</p>
                </div>
            </div>
            <div class="row stats-row display-xl-max-none">
                <div class="col-auto">
                    <p>{$authorUsername} | {$date}</p>
                </div>
                <div class="col-auto stats-container">
                    <p class="like-counter {$likeClass}">{$beitrag->getLikes()}</p>
                    <i class="fa-solid fa-heart like-button mute-cursor-effects {$likeClass}"></i>
                </div>
                <div class="col-auto stats-container">
                    <p>{$beitrag->getViews()}</p><i class="fa-regular fa-eye"></i>
                </div>
            </div>
        </div>
        <div class="row display-xl-none display-md-max-none">
            <div class="col-md-5">
                <p>{$authorUsername} | {$date}</p>
            </div>
            <div class="col-md-5">
                <div class="row stats-row">
                    <div class="col-auto stats-container">
                        <p class="like-counter {$likeClass}">{$beitrag->getLikes()}</p>
                        <i class="fa-solid fa-heart like-button mute-cursor-effects {$likeClass}"></i>
                    </div>
                    <div class="col-auto stats-container">
                        <p>{$beitrag->getViews()}</p><i class="fa-regular fa-eye"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {$tagsHTML}
</div>
HTML;
}