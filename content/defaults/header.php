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
            $searchGETCacheHTML .= '<input id="navbar-search-cache-'.$key.'" type="hidden" name="'.$key.'" value="'.$value.'">';
        }
    }
}
?>
<nav class="navbar-container" id="navbar-container">
    <div class="navbar-mobile-row">
        <i class="cursor-pointer navbar-icon col-auto fa-solid fa-magnifying-glass" id="navbar-search-icon"></i>
        <button id="navbar-button" class="navbar-dropdown-button dropdown-button col-auto" data-dropdown-target="navbar-collapse">
            <i class="navbar-icon nav-hamburger-icon col-auto fa-solid fa-bars" id="navbar-dropdown-icon"></i>
        </button>
    </div>
    <div class="dropdown-collapse collapsed" id="navbar-collapse">
        <div class="navbar row">
            <ul class="navbar-col navbar-row navbar-list">
                <i class="col-auto fa-solid fa-cat"></i>
                <li class="navbar-item">
                    <a href="index" class="navbar-link <?php if(empty($_GET["p"]) || $_GET["p"] == "index") echo "active";?>">Home</a>
                </li>
                <li class="navbar-item">
                    <a href="katzegorien" class="navbar-link <?php if($_GET["p"] == "katzegorien") echo "active";?>">Katzegorien</a>
                </li>
                <li class="navbar-item">
                    <a href="neuePosts" class="navbar-link <?php if($_GET["p"] == "neuePosts") echo "active";?>">Neue Beiträge</a>
                </li>
                <?php if (isLoggedin()): ?>
                    <li class="navbar-item">
                        <a href="post" class="navbar-link <?php if($_GET["p"] == "post") echo "active";?>">Miau posten</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="navbar-col align-items-center navbar-row">
                <div class="navbar-col">
                    <form method="GET" action="katzegorien">
                        <input id="navbar-search-title" type="text" class="input" name="title-search" placeholder="Titel Suche" value="<?php echo $searchTitle; ?>">
                        <?php echo $searchGETCacheHTML; ?>
                    </form>
                </div>
                <?php if (!isLoggedin()): ?>
                    <div class="navbar-col navbar-row">
                        <button class="navbar-item-sm button primary-button button-accent2 login-button" onclick='window.location.href="/login";'>
                            <div class="row align-items-center login-button-row">
                                <p class="col-auto">Login</p>
                                <i class="col-auto fa-solid fa-right-to-bracket"></i>
                            </div>
                        </button>
                    </div>
                    <div class="navbar-col navbar-row">
                        <button class="navbar-item-sm button primary-button button-accent2 login-button" onclick='window.location.href="/registrieren";'>
                            <div class="row align-items-center login-button-row">
                                <p class="col-auto">Registrieren</p>
                                <i class="col-auto fa-solid fa-arrow-up-from-bracket"></i>
                            </div>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="navbar-col navbar-row">
                        <button class="navbar-item-sm button primary-button button-accent2 login-button" onclick='window.location.href="/user";'>
                            <div class="row align-items-center login-button-row">
                                <i class="col-auto fa-solid fa-user"></i>
                                <p class="col-auto"><?php echo getSessionUsername(); ?></p>
                            </div>
                        </button>
                    </div>
                    <div class="navbar-col navbar-row">
                        <button class="navbar-item-sm button primary-button button-remove login-button" onclick='window.location.href="/src/application/logout.php?redirect="+window.location.href;'>
                            <div class="row align-items-center login-button-row">
                                <p class="col-auto">Abmelden</p>
                                <i class="col-auto fa-solid fa-right-from-bracket"></i>
                            </div>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<script>
    let navbarButton = document.getElementById("navbar-button");
    let navbarCollapse = document.getElementById("navbar-collapse");
    let navbarSearchIcon = document.getElementById("navbar-search-icon");
    let navbarContainer = document.getElementById("navbar-container");
    let navbarSearchInput = document.getElementById("navbar-search-title");
    let navbarSearchPageCache = document.getElementById("navbar-search-cache-page");

    let originalSearchValue = navbarSearchInput.value;
    let originalSearchPage = navbarSearchPageCache != null ? navbarSearchPageCache.value : 1;

    navbarSearchInput.addEventListener("input", e => {
        if (navbarSearchPageCache == null) return;

        if (navbarSearchInput.value !== originalSearchValue) {
            navbarSearchPageCache.value = 1;
        } else {
            navbarSearchPageCache.value = originalSearchPage;
        }
    });

    navbarSearchIcon.addEventListener("click", e => {
       window.location.href = "/katzegorien";
    });

    window.addEventListener('resize', e => handleNavbarResizing(e));
    document.addEventListener("DOMContentLoaded", e => handleNavbarResizing(e));

    let dropdownIcon = document.getElementById("navbar-dropdown-icon");
    dropdownIcon.addEventListener("click", e => {
        if (dropdownIcon.classList.contains("fa-x")) {
            collapsedState();
        } else if (dropdownIcon.classList.contains("fa-bars")) {
            expandedState();
        }
    });

    function handleNavbarResizing(e) {
        let width = window.innerWidth || document.documentElement.clientWidth;

        if (width < 951) {
            navbarButton.classList.add("collapsed");
            navbarCollapse.classList.add("collapsed");
            collapsedState();
        } else {
            navbarButton.classList.remove("collapsed");
            navbarCollapse.classList.remove("collapsed");
            expandedState();
            navbarContainer.classList.add("collapsed");
        }
    }

    function collapsedState() {
        dropdownIcon.classList.remove("fa-x");
        dropdownIcon.classList.add("fa-bars");
        navbarContainer.classList.add("collapsed");
    }

    function expandedState() {
        dropdownIcon.classList.add("fa-x");
        dropdownIcon.classList.remove("fa-bars");
        navbarContainer.classList.remove("collapsed");
    }
</script>
