<?php
require_once (BASE_PATH."/src/application/sessionFunctions.php");

/*
 * hidden inputs containing the GET values for search if there were any
 * This is needed to preserve paging and search values when the user comes from a search
 */
$searchGETCacheHTML = "";
$searchTitle = $_GET["title-search"] ?? "";

if (isset($_GET["title-search"])) {
    $getCopy = $_GET;
    unset($getCopy["p"]);
    unset($getCopy["id"]);
    unset($getCopy["title-search"]);

    foreach ($getCopy as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $valueKey => $valueItem) {
                $searchGETCacheHTML .= '<input type="hidden" name="'.$key.'[]" value="'.$valueItem.'">';
            }
        } else {
            $searchGETCacheHTML .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
        }
    }
}
?>

<nav class="navbar">
    <ul class="navbar-list">
        <li class="navbar-item">
            <a href="index" class="navbar-link <?php if(empty($_GET["p"]) || $_GET["p"] == "index") echo "active";?>">Home</a>
        </li>
        <li class="navbar-item">
            <a href="katzegorien" class="navbar-link <?php if($_GET["p"] == "katzegorien") echo "active";?>">Katzegorien</a>
        </li>
        <?php if (isLoggedin()): ?>
            <li class="navbar-item">
                <a href="post" class="navbar-link <?php if($_GET["p"] == "post") echo "active";?>">Miau posten</a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="row align-items-center justify-content-space-between navbar-row">
        <div class="col-auto">
            <form method="GET" action="katzegorien">
                <input type="text" class="input" name="title-search" placeholder="Titel Suche" value="<?php echo $searchTitle; ?>">
                <?php echo $searchGETCacheHTML; ?>
            </form>
        </div>
        <?php if (!isLoggedin()): ?>
            <div class="col-auto">
                <button class="button primary-button button-accent2 login-button" onclick='window.location.href="/login";'>
                    <div class="row align-items-center login-button-row">
                        <p class="col-auto">Login</p>
                        <i class="col-auto fa-solid fa-right-to-bracket"></i>
                    </div>
                </button>
            </div>
            <div class="col-auto">
                <button class="button primary-button button-accent2 login-button" onclick='window.location.href="/registrieren";'>
                    <div class="row align-items-center login-button-row">
                        <p class="col-auto">Registrieren</p>
                        <i class="col-auto fa-solid fa-arrow-up-from-bracket"></i>
                    </div>
                </button>
            </div>
        <?php else: ?>
            <div class="col-auto">
                <button class="button primary-button button-accent2 login-button" onclick='window.location.href="/user";'>
                    <div class="row align-items-center login-button-row">
                        <i class="col-auto fa-solid fa-user"></i>
                        <p class="col-auto"><?php echo getSessionUsername(); ?></p>
                    </div>
                </button>
            </div>
            <div class="col-auto">
                <button class="button primary-button button-remove login-button" onclick='window.location.href="/src/application/logout.php?redirect="+window.location.href;'>
                    <div class="row align-items-center login-button-row">
                        <p class="col-auto">Abmelden</p>
                        <i class="col-auto fa-solid fa-right-from-bracket"></i>
                    </div>
                </button>
            </div>
        <?php endif; ?>
    </div>
</nav>