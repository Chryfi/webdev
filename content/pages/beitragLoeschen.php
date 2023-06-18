<?php
require_once(BASE_PATH . "/src/utils/sessionFunctions.php");
require_once(BASE_PATH . "/src/datalayer/tables/beitrag.php");

/* quick access for output */
$title = "";
$id = -1;
$isUserAuthor = false;

if (isset($_GET["id"]) && $_GET["id"] != "" && is_numeric($_GET["id"]) && isLoggedin()) {
    $id = $_GET["id"];
    $db = getKatzenBlogDatabase();
    $beitragTable = new BeitragTable($db);

    $beitrag = $beitragTable->getBeitrag($id);

    if ($beitrag) {
        $isUserAuthor = $beitrag->getUserId() == getSessionUserId();
        $title = $beitrag->getTitle();
    }

    $db->disconnect();
}

?>

<div class="page-wrapper">
    <header class="container-sm">
        <div class="header-text">
            <h1>Löschen</h1>
            <p class="lead">Lösche deinen ausgewählten Beitrag.</p>
        </div>
    </header>
    <main class="container-sm">
        <div class="round-container register-container col-xl-6">
            <?php if ($isUserAuthor): ?>
                <h2 class="h2">Möchtest du den Beitrag wirklich Löschen?</h2>
                <h1 class="h1 lead">"<?php echo $title; ?>"</h1>
                <form id="deletion-form" class="register-form" method="POST">
                    <div class="row justify-content-center">
                        <div class="row confirmation-row">
                            <div class="col-auto">
                                <button id="abort-delete-button"
                                        class="button primary-button button-accent2 white-text">
                                    Nicht löschen
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="button primary-button button-remove white-text"">
                                <div class="row align-items-center">
                                    <i class="col-auto fa-solid fa-trash"></i>
                                    <p class="col-auto icon-text-right">Löschen</p>
                                </div>
                                </button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="delete" value="<?php echo $id; ?>">
                </form>
            <?php else: ?>
                <p class="lead text-center"><i class="fa-solid fa-circle-exclamation"></i> Dieser Beitrag existiert nicht oder er gehört dir nicht!</p>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
    let formElement = document.getElementById("deletion-form");

    if (formElement != null) {
        document.getElementById("abort-delete-button").addEventListener("click", e => {
            e.preventDefault();

            window.location.href = "/article?id=<?php echo $id;?>";
        });

        formElement.addEventListener("submit", e => {
            e.preventDefault();

            let formData = new FormData(formElement);

            fetch("/src/application/deleteBeitrag.php", {
                method: "POST",
                body: formData
            }).then(async response => {
                let status = await response.json();

                let messageElement = document.createElement("p");
                messageElement.classList.add("lead", "text-center");

                if (status["status"]) {
                    messageElement.textContent = "Löschung erfolgreich.";
                } else {
                    messageElement.textContent = "Löschung nicht erfolgreich.";
                }

                formElement.replaceWith(messageElement);
            });
        });
    }
</script>