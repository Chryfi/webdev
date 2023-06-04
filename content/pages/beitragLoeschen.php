<?php
require_once(BASE_PATH . "/src/application/sessionFunctions.php");
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
    <main class="container-sm display-flex justify-content-center">
        <div class="round-container register-container col-xl-6 w-100">
            <?php if ($isUserAuthor): ?>
                <h2 class="h2">Möchtest du den Beitrag wirklich Löschen?</h2>
                <h1 class="h1 lead">"<?php echo $title; ?>"</h1>
                <form id="deletion-form" class="register-form" method="POST">
                    <div class="row justify-content-center">
                            <div class="row confirmation-row">
                                <div class="col-auto">
                                    <button id="abort-delete-button"
                                            class="button primary-button button-accent2 login-button">
                                        Nicht löschen
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="button primary-button button-remove login-button"
                                    ">
                                    <div class="row align-items-center login-button-row">
                                        <i class="col-auto fa-solid fa-trash"></i>
                                        <p class="col-auto">Löschen</p>
                                    </div>
                                    </button>
                                </div>
                        </div>
                    </div>
                    <input type="hidden" name="delete" value="<?php echo $id; ?>">
                </form>
            <?php else: ?>
                <h2 class="h2">Dieser Beitrag existiert nicht oder er gehört dir nicht!</h2>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
    document.getElementById("abort-delete-button").addEventListener("click", e => {
        e.preventDefault();

        window.location.href = "/article?id=<?php echo $id;?>";
    });

    let formElement = document.getElementById("deletion-form");
    formElement.addEventListener("submit", e => {
        e.preventDefault();

        let formData = new FormData(e.target);

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
</script>