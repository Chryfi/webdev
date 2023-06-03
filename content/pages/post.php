<?php
require_once (BASE_PATH."/src/application/sessionFunctions.php");
require_once (BASE_PATH."/src/application/userForm.php");
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/datalayer/tables/kategorie.php");
require_once (BASE_PATH."/src/utils/tags.php");

/* quick access for output */
$title = $_POST["title"] ?? "";
$spoiler = $_POST["spoiler"] ?? "";
$tags = $_POST["tags"] ?? [];
$content = $_POST["content"] ?? "";
$imageName = $_POST["image-name-cache"] ?? "";
$imageData = $_POST["image-data-cache"] ?? "";

/* error messages for the input names */
$errors = [];

if (isset($_POST["title"]) && isLoggedin())
{
    $textPosts = ["title", "spoiler", "content"];

    foreach ($textPosts as $key) {
        if (!isset($_POST[$key]) || $_POST[$key] == "") {
            $errors[$key] = "Geben Sie etwas ein.";
        }
    }

    /* correct tags e.g. duplicate entries and make lowercase */
    if (isset($_POST["tags"])) {
        $tags = array_unique(array_map('strtolower', $tags));
    }

    /* count the corrected tags as duplicate entries might be removed and result in not enough tags */
    if (!isset($tags) || count($tags) < 2) {
        $errors["tags"] = "Nutzen Sie mindestens zwei tags.";
    } else if (count($tags) > 10) {
        $errors["tags"] = "Es sind maximal 10 tags erlaubt.";
    }

    if ($imageData == "") {
        $errors["image"] = "Laden Sie ein Bild hoch.";
    } else if ($errorMsg = validateImage($imageData)) {
        $errors["image"] = $errorMsg;
    }

    if (count($errors) == 0 && insertBeitrag($title, $spoiler, $tags, $content, $imageData)) {
        echo '<script>
                window.location.href = \'/article\';
            </script>';
    }
}

function insertBeitrag($title, $spoiler, $tags, $content, $imageBase64URI) : bool {
    $base64Image = explode(',', $imageBase64URI)[1];
    $imageDataDecoded = base64_decode($base64Image);
    $extension = explode('/', mime_content_type($imageBase64URI))[1];
    $fileName = uniqid() . '.' . $extension;

    $db = getKatzenBlogDatabase();
    $kategorieTable = new KategorieTable($db);
    $beitragTable = new BeitragTable($db);
    $beitrag = Beitrag::createNecessary($title, $spoiler, $content, getSessionUserId(), BASE_PATH."/upload/".$fileName);

    // what if not enough tags are inserted due to errors? Delete beitrag?
    if ($beitragTable->insertBeitrag($beitrag)) {
        foreach ($tags as $tag) {
            $kategorieTable->insertTag($tag, $beitrag->getId());
        }

        file_put_contents(BASE_PATH."/upload/".$fileName, $imageDataDecoded);

        return true;
    }

    $db->disconnect();

    return false;
}


function validateImage($imageFile) : ?string {
    $extension = explode('/', mime_content_type($imageFile))[1];

    if ($extension != "png" && $extension != "jpg" && $extension != "jpeg") {
        return "Ungültiges Dateiformat";
    }

    return null;
}
?>

<div class="page-wrapper">
    <header>
        <div class="header-text">
            <h1>Blog Beitrag</h1>
            <p class="lead">Teile der Katzenwelt deine Gedanken mit!</p>
        </div>
    </header>
    <main class="container-sm">
        <div class="round-container">
            <?php if (!isLoggedin()): ?>
                <p class="lead text-center"><i class="fa-solid fa-circle-exclamation"></i> Melde dich zuerst an um einen Blog Beitrag zu erstellen.</p>
            <?php else: ?>
                <form class="blog-post" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="MAX_FILE_SIZE" value="5000000">
                    <div class="row">
                        <div class="col">
                            <p class="lead">Titel</p>
                            <input class="input" type="text" name="title" value="<?php echo $title;?>">
                            <?php outputError("title", $errors);?>
                        </div>
                    </div>
                    <div class="row margin-y-1">
                        <div class="col">
                            <p class="lead">Spoiler</p>
                            <div class="grow-wrap">
                                <textarea id="spoiler-textarea" name="spoiler" class="input"
                                          onInput="this.parentNode.dataset.replicatedValue = this.value"><?php echo $spoiler;?></textarea>
                            </div>
                            <?php outputError("spoiler", $errors);?>
                        </div>
                    </div>
                    <div class="row margin-y-1">
                        <div class="col">
                            <p class="lead">Tags</p>
                            <input type="text" class="input" id="tag-search-input" autocomplete="off">
                            <?php outputError("tags", $errors);?>
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
                    <div class="row margin-y-1">
                        <div class="col">
                            <p class="lead">Beitrag</p>
                            <div class="grow-wrap">
                                <textarea id="content-textarea" name="content" class="input"
                                          onInput="this.parentNode.dataset.replicatedValue = this.value"><?php echo $content;?></textarea>
                            </div>
                            <?php outputError("content", $errors);?>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-sm-8" id="upload-container">
                            <input id="image-data-cache" type="text" name="image-data-cache" hidden value="<?php echo $imageData;?>">
                            <input id="image-name-cache" type="text" name="image-name-cache" hidden value="<?php echo $imageName;?>">
                            <?php outputError("image", $errors);?>
                            <!-- Upload element gets inserted here -->
                        </div>
                        <div class="col-sm-5" style="height: 0px">
                            <img class="blog-thumbnail-img" id="image-preview" src="<?php echo $imageData;?>">
                        </div>
                    </div>
                    <hr class="hr">
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <button type="submit" class="button button-primary button-accent2">Posten</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
    let imagePreview = document.getElementById("image-preview");
    let tagList = document.getElementById("tag-list");
    let tagSearchInput = document.getElementById("tag-search-input");

    let tagSearchHandler = new TagSearch(tagSearchInput, tagList);

    let uploadElement = new UploadElement(["image/jpeg", "image/png"]);
    /* upload the data as base64 URI so we can persist it and render it easily */
    uploadElement.fileInputCache = document.getElementById("image-data-cache");
    uploadElement.onLoad = onUploadLoad;

    document.getElementById("upload-container").appendChild(uploadElement.getElement());


    /* when PHP outputs stuff into the input areas, we need to adjust the styles and do some extra work */
    document.addEventListener("DOMContentLoaded", e => {
        let spoilerTextarea = document.getElementById("spoiler-textarea");
        let contentTextarea = document.getElementById("content-textarea");
        let imagePreview = document.getElementById("image-preview");
        let imageNameCache = document.getElementById("image-name-cache");

        spoilerTextarea.parentNode.dataset.replicatedValue = spoilerTextarea.value;
        contentTextarea.parentNode.dataset.replicatedValue = contentTextarea.value;

        /* when PHP loaded the image URI from the last POST request */
        if (imagePreview.getAttribute("src") !== "") {
            imagePreview.parentElement.style.height = "";
            imagePreview.parentElement.style.marginTop = "1em";
            uploadElement.uploadURI(imagePreview.src, new File([], imageNameCache.value));
        }
    });


    /**
     * when uploading an image -> validate image aspect ratio.
     * @param {UploadElement} element
     */
    function onUploadLoad(element) {
        return new Promise(resolve => {
            /* read image to check aspect ratio */
            const img = new Image();
            img.onload = () => {
                const width = img.naturalWidth;
                const height = img.naturalHeight;
                const heightTest = (height / width) * 1920;

                if (Math.round(heightTest) !== 1080) {
                    element.displayError("Seitenverhältnisse stimmen nicht. Es ist nur 16:9 erlaubt.");

                    resolve(false);

                    return;
                }

                imagePreview.src = element.fileResult;
                imagePreview.parentElement.style.height = "";
                imagePreview.parentElement.style.marginTop = "1em";

                document.getElementById("image-name-cache").value = element.file.name;

                resolve(true);
            }

            img.src = element.fileResult;
        });
    }
</script>