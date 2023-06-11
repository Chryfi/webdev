<?php
require_once (BASE_PATH."/src/utils/sessionFunctions.php");
require_once (BASE_PATH."/src/utils/userForm.php");
require_once (BASE_PATH."/src/datalayer/tables/beitrag.php");
require_once (BASE_PATH."/src/datalayer/tables/kategorie.php");
require_once (BASE_PATH."/src/utils/tags.php");
require_once (BASE_PATH."/src/utils/redirect.php");

/* Whether we are editing an existing article */
$isEditMode = false;
/* retrieved from the database */
$oldBeitrag = null;

/* quick access for output */
$title = $_POST["title"] ?? "";
$spoiler = $_POST["spoiler"] ?? "";
$tags = $_POST["tags"] ?? [];
$content = $_POST["content"] ?? "";
$imageName = $_POST["image-name-cache"] ?? "";
$imageData = $_POST["image-data-cache"] ?? "";

if (isset($_GET["edit"]) && $_GET["edit"] != ""
    && is_numeric($_GET["edit"]) && isLoggedin())
{
    $db = getKatzenBlogDatabase();
    $beitragTable = new BeitragTable($db);

    $oldBeitrag = $beitragTable->getBeitragRelations($_GET["edit"]);

    if ($oldBeitrag && $oldBeitrag->getUserId() == getSessionUserId()) {
        $isEditMode = true;

        $title = $_POST["title"] ?? $oldBeitrag->getTitle();
        $spoiler = $_POST["spoiler"] ?? $oldBeitrag->getTeaser();
        $tags = $_POST["tags"] ?? $oldBeitrag->getTags();
        $content = $_POST["content"] ?? $oldBeitrag->getContent();
        $imageName = $_POST["image-name-cache"] ?? "Image";
        $imageData = $_POST["image-data-cache"] ?? (readAsBase64URI($oldBeitrag->getImageName()) ?? "");
    }

    $db->disconnect();
}

/* error messages for the input names */
$errors = [];

if (isset($_POST["title"]) && isLoggedin())
{
    $textPosts = ["title", "spoiler", "content"];

    foreach ($textPosts as $key) {
        if (!isset($_POST[$key]) || $_POST[$key] == "") {
            $errors[$key] = "Gebe etwas ein.";
        }
    }

    /* correct tags e.g. remove duplicate entries and make lowercase */
    if (isset($_POST["tags"])) {
        $tags = array_unique(array_map('strtolower', $tags));
        /* remove spaces from the beginning and end of all tags */
        $tags = array_map("trim", $tags);
    }

    /* count the corrected tags as duplicate entries might be removed and result in not enough tags */
    if (!isset($tags) || count($tags) < 2) {
        $errors["tags"] = "Nutze mindestens zwei tags.";
    } else if (count($tags) > 10) {
        $errors["tags"] = "Es sind maximal 10 tags erlaubt.";
    }

    if ($imageData == "") {
        $errors["image"] = "Lade ein Bild hoch.";
    } else if ($errorMsg = validateImage($imageData)) {
        $errors["image"] = $errorMsg;
    }

    if (strlen($spoiler) > 350) {
        $errors["spoiler"] = "Der Spoiler darf maximal 350 Zeichen enthalten.";
    }

    if (strlen($title) > 75) {
        $errors["title"] = "Der Titel darf maximal 75 Zeichen enthalten.";
    }

    if (count($errors) == 0) {
        $beitrag = null;

        if (!$isEditMode) {
            $beitrag = insertBeitrag($title, $spoiler, $tags, $content, $imageData);
        } else {
            $beitrag = editBeitrag($oldBeitrag, $title, $spoiler, $tags, $content, $imageData);
        }

        if ($beitrag) redirectJS("article?id=".$beitrag->getId());
    }
}




function insertBeitrag($title, $spoiler, $tags, $content, $imageBase64URI) : ?Beitrag {
    $imagePath = generateThumbnailPath($imageBase64URI);
    $db = getKatzenBlogDatabase();
    $kategorieTable = new KategorieTable($db);
    $beitragTable = new BeitragTable($db);
    $beitrag = Beitrag::createNecessary($title, $spoiler, $content, getSessionUserId(), $imagePath);

    if ($beitragTable->insertBeitrag($beitrag)) {
        foreach ($tags as $tag) {
            $kategorieTable->insertTag($tag, $beitrag->getId());
        }

        saveThumbnail($imageBase64URI, $imagePath);

        $db->disconnect();
        return $beitrag;
    }

    $db->disconnect();

    return null;
}

function editBeitrag(Beitrag $oldBeitrag, $title, $spoiler, $tags, $content, $imageBase64URI) : ?Beitrag {
    $oldImagePath = $oldBeitrag->getImageName();
    $imagePath = $oldImagePath;
    $oldImage = readAsBase64URI($oldBeitrag->getImageName());

    if ($oldImage != $imageBase64URI) {
        $imagePath = generateThumbnailPath($imageBase64URI);
    }

    $db = getKatzenBlogDatabase();
    $kategorieTable = new KategorieTable($db);
    $beitragTable = new BeitragTable($db);
    $beitrag = Beitrag::createNecessary($title, $spoiler, $content, getSessionUserId(), $imagePath);
    $beitrag->setId($oldBeitrag->getId());

    if ($beitragTable->updateBeitrag($beitrag)) {
        $oldTags = $kategorieTable->getTagsByBeitrag($oldBeitrag->getId());

        if ($oldTags != null) {
            /* add new tags */
            foreach ($tags as $tag) {
                if (!in_array($tag, $oldTags)) {
                    $kategorieTable->insertTag($tag, $beitrag->getId());
                }
            }
            /* remove tags if necessary */
            foreach ($oldTags as $oldTag) {
                if (!in_array($oldTag, $tags)) {
                    $kategorieTable->removeTagFromBeitrag($oldTag, $beitrag->getId());
                }
            }
        }

        if ($oldImage != $imageBase64URI) {
            unlink($oldImagePath);
            saveThumbnail($imageBase64URI, $imagePath);
        }

        $db->disconnect();

        return $beitrag;
    }

    $db->disconnect();

    return null;
}

function generateThumbnailPath($imageBase64URI) : string {
    $extension = explode('/', mime_content_type($imageBase64URI))[1];
    $fileName = uniqid() . '.' . $extension;
    return BASE_PATH."/upload/".$fileName;
}

function saveThumbnail($imageBase64URI, $path): void {
    $base64Image = explode(',', $imageBase64URI)[1];
    $imageDataDecoded = base64_decode($base64Image);

    file_put_contents($path, $imageDataDecoded);
}

function readAsBase64URI($path) : ?string {
    if (!is_file($path)) return null;

    $imageFileRS = fopen($path, 'r');
    $fileContent = fread($imageFileRS, filesize($path));
    fclose($imageFileRS);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $fileContent);
    finfo_close($finfo);

    $base64Content = base64_encode($fileContent);
    return 'data:' . $mimeType . ';base64,' . $base64Content;
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
    <header class="container-sm">
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
                <form class="blog-post" method="POST" enctype="multipart/form-data" action="<?php if($isEditMode) echo 'post?edit='.$_GET["edit"]; ?>">
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
                            <input type="text" class="input" id="tag-search-input" autocomplete="off" inputmode="search" placeholder="Suche nach Tags oder erstelle neue">
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
                            <!-- Upload element gets inserted here -->
                            <?php outputError("image", $errors);?>
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

    document.getElementById("image-name-cache").insertAdjacentElement('afterend', uploadElement.getElement());


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

                /* account for rounding error */
                if (heightTest <= 1079 || heightTest >= 1081) {
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