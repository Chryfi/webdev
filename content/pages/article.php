<?php
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/datalayer/tables/user.php");
require_once (BASE_PATH."/src/datalayer/tables/kategorie.php");
require_once (BASE_PATH."/src/datalayer/tables/likedTable.php");
require_once (BASE_PATH."/src/utils/redirect.php");
require_once (BASE_PATH."/src/application/sessionFunctions.php");
require_once (BASE_PATH."/src/utils/tags.php");

/* quick access for output */
$title = "";
$spoiler = "";
$authorName = "";
$creationDate = "";
$tags = array();
$viewCount = 0;
$likes = 0;
$imageLink = "";
$content = "";
$userLikedThis = false;

if (isset($_GET["id"])) {
    $db = getKatzenBlogDatabase();
    $beitragTable = new BeitragTable($db);
    $userTable = new UserTable($db);
    $kategorieTable = new KategorieTable($db);
    $likedTable = new LikedTable($db);

    if (isLoggedin()) {
        $userLikedThis = $likedTable->isLikePresent($_GET["id"], getSessionUserId());
    }

    if ($beitrag = $beitragTable->getBeitrag((int) $_GET["id"])) {
        if (!isLoggedin() || ($beitrag->getUserId() != getSessionUserId())) {
            $beitragTable->addView($beitrag->getId());
        }


        $user = $userTable->getById($beitrag->getUserId());
        $authorName = $user ? $user->getUsername() : "";

        $tags = $kategorieTable->getTagsByBeitrag($beitrag->getId()) ?? array();
        $likes = $likedTable->countLikesBeitrag($beitrag->getId()) ?? 0;

        $title = $beitrag->getTitle();
        $spoiler = "<pre>".$beitrag->getTeaser()."</pre>";
        $viewCount = $beitrag->getViews();
        $creationDate = date("H:i:s d.m.Y",$beitrag->getDateTime());
        $content = "<pre>".$beitrag->getContent()."</pre>";
        $imageLink = str_replace(BASE_PATH, "", $beitrag->getImageName());
    } else {
        redirectJS("./");
        $db->disconnect();
    }

    $db->disconnect();
} else {
    redirectJS("./");
}
?>
<div class="page-wrapper">
    <main class="container-sm">
        <?php if(isset($_GET["id"])): ?>
            <div class="round-container blog-container">
                <div class="blog-head row justify-content-center">
                    <div class="col-md-8">
                        <h2><?php echo $title;?></h2>
                        <?php echo $spoiler;?>
                        <div class="row blog-info-container">
                            <div class="col-md-5">
                                <p><i class="col-auto fa-solid fa-user"></i> <?php echo $authorName;?> | <?php echo $creationDate;?></p>
                            </div>
                            <div class="col-md-5">
                                <div class="row stats-row">
                                    <div class="col-auto stats-container">
                                        <p id="like-counter" class="like-counter <?php if ($userLikedThis) echo "active"; ?>"><?php echo $likes;?></p>
                                        <i class="fa-solid fa-heart like-button <?php if ($userLikedThis) echo "active"; ?>" id="like-button"></i>
                                    </div>
                                    <div class="col-auto stats-container">
                                        <p><?php echo $viewCount;?></p><i class="fa-regular fa-eye"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <button class="display-sm-none dropdown-button row align-items-center collapsed" data-dropdown-target="tag-list">
                                    <i class="col-auto fa-solid fa-tags"></i>
                                    <div class="col-auto">&nbsp;Tag Liste</div>
                                </button>
                                <div class="tag-list-container dropdown-collapse collapsed" id="tag-list">
                                    <div class="tag-list-container row align-items-center justify-content-space-between dropdown-body">
                                        <i class="col-auto fa-solid fa-tags display-sm-max-none"></i>
                                        <div class="col">
                                            <div class="row tag-list">
                                                <?php outputSimpleTagList($tags); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <img class="blog-thumbnail-img" src="<?php echo $imageLink; ?>">
                <div class="row justify-content-center blog-content-container">
                    <div class="col-md-8">
                        <?php echo $content;?>
                        <div class="row blog-info-container">
                            <div class="col-md-5">
                                <p><i class="col-auto fa-solid fa-user"></i> <?php echo $authorName;?> | <?php echo $creationDate;?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
<script>
    let likeButtonElement = document.getElementById("like-button");
    let likeCounterElement = document.getElementById("like-counter");

    /**
     * When clicking the like button, the hover effect for removing the like should
     * only appear after leaving and re-entering the like button. Similarily if the user removed the like, 
     * the hover effects for adding a like should only appear after re-entering the like button.
     */
    likeButtonElement.addEventListener("click", e => {
        const urlParams = new URLSearchParams(window.location.search);
        const articleID = urlParams.get('id');

        let likeRequest = new FormData();
        likeRequest.append("id", articleID);

        if (likeButtonElement.classList.contains("active")) {
            likeRequest.append("remove", "");

            fetch("/src/application/like.php", {
                method: "POST",
                body: likeRequest
            }).then(async (response) => {
                let status = await response.json();

                if (!status["status"]) return;

                likeCounterElement.textContent = parseInt(likeCounterElement.textContent) - 1;
                likeButtonElement.classList.remove("active");
                likeCounterElement.classList.remove("active");
                likeButtonElement.classList.remove("like-hover");
            });
        } else {
            likeRequest.append("add", "");

            fetch("/src/application/like.php", {
                method: "POST",
                body: likeRequest
            }).then(async (response) => {
                let status = await response.json();

                if (!status["status"]) return;

                likeCounterElement.textContent = parseInt(likeCounterElement.textContent) + 1;
                likeButtonElement.classList.add("active");
                likeCounterElement.classList.add("active");
                likeButtonElement.classList.remove("like-hover");
            });
        }
    });

    likeButtonElement.addEventListener("mouseleave", e => {
        likeButtonElement.classList.remove("like-hover");
    });

    likeButtonElement.addEventListener("mouseenter", e => {
        likeButtonElement.classList.add("like-hover");
    });
</script>