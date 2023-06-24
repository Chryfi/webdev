<?php
require_once (BASE_PATH."/src/utils/sessionFunctions.php");

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
    <div class="navbar navbar-mobile">
        <button id="navbar-search-button" class="display-flex align-items-center bg-transparent col-auto">
            <i class="col-auto fa-solid fa-magnifying-glass"></i>
        </button>
        <button id="navbar-button" class="display-flex align-items-center bg-transparent dropdown-button col-auto" data-dropdown-target="navbar-collapse">
            <i class="nav-hamburger-icon col-auto fa-solid fa-bars" id="navbar-dropdown-icon"></i>
        </button>
    </div>
    <div class="dropdown-collapse collapsed" id="navbar-collapse">
        <div class="navbar navbar-body row">
            <ul class="navbar-col navbar-row navbar-list">
                <i class="col-auto fa-solid fa-cat"></i>
                <li class="navbar-item">
                    <a href="/index" class="navbar-link <?php if(empty($_GET["p"]) || $_GET["p"] == "index") echo "active";?>">Home</a>
                </li>
                <li class="navbar-item">
                    <a href="/katzegorien" class="navbar-link <?php if($_GET["p"] == "katzegorien") echo "active";?>">Katzegorien</a>
                </li>
                <li class="navbar-item">
                    <a href="/neuePosts" class="navbar-link <?php if($_GET["p"] == "neuePosts") echo "active";?>">Neue Beiträge</a>
                </li>
                <li class="navbar-item">
                    <a href="/post" class="navbar-link <?php if($_GET["p"] == "post") echo "active";?>">Miau posten</a>
                </li>
            </ul>
            <div class="navbar-col align-items-center navbar-row">
                <div class="navbar-col">
                    <form method="GET" action="katzegorien">
                        <input id="navbar-search-title" type="text" class="input" name="title-search" placeholder="Titel Suche" value="<?php echo $searchTitle; ?>">
                        <?php echo $searchGETCacheHTML; ?>
                    </form>
                </div>
                <?php if (!isLoggedin()): ?>
                    <div class="navbar-col navbar-item row">
                        <button class="col-sm-auto button primary-button button-accent2 white-text" onclick='window.location.href="/login";'>
                            <div class="row align-items-center">
                                <p class="col-auto icon-text-left">Login</p>
                                <i class="col-auto fa-solid fa-right-to-bracket"></i>
                            </div>
                        </button>
                    </div>
                    <div class="navbar-col navbar-item row">
                        <button class="col-sm-auto button primary-button button-accent2 white-text" onclick='window.location.href="/registrieren";'>
                            <div class="row align-items-center">
                                <p class="col-auto icon-text-left">Registrieren</p>
                                <i class="col-auto fa-solid fa-arrow-up-from-bracket"></i>
                            </div>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="navbar-col navbar-item row">
                        <div class="user-info-container col-sm-auto">
                            <button id="user-button" class="dropdown-button user-dropdown-button dropdown-button-arrow collapsed" data-dropdown-target="userdropdown">
                                <div class="button button-accent user-icon">
                                    <i class="col-auto fa-solid fa-user"></i>
                                </div>
                                <a class="col-auto navbar-username"><?php echo getSessionUsername(); ?></a>
                            </button>
                            <div id="userdropdown" class="user-nav-box-container user-dopdown-container dropdown-collapse collapsed">
                                <div class="user-nav-box">
                                    <ul class="user-nav-row row">
                                        <li>
                                            <p class="col-auto">Angemeldet als <?php echo getSessionUsername(); ?></p>
                                        </li>
                                        <hr>
                                        <li>
                                            <a href="/user" class="navbar-link <?php if($_GET["p"] == "user") echo "active";?>">Dein Account</a>
                                        </li>
                                        <hr>
                                        <li class="row justify-content-center">
                                            <button class="col-auto button primary-button button-remove white-text"
                                                    onclick='window.location.href="/src/application/logout.php?redirect="+window.location.href;'>
                                                <div class="row align-items-center">
                                                    <p class="col-auto icon-text-left">Abmelden</p>
                                                    <i class="col-auto fa-solid fa-right-from-bracket"></i>
                                                </div>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="navbar-mobile-element navbar-col navbar-item row">
                        <button class="col-sm-auto button primary-button button-remove white-text" onclick='window.location.href="/src/application/logout.php?redirect="+window.location.href;'>
                            <div class="row align-items-center">
                                <p class="col-auto icon-text-left">Abmelden</p>
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
    let lastWidth = 0;
    const resizeNavMaxWidth = 921;
    const navbarButton = document.getElementById("navbar-button");
    const navbarCollapse = document.getElementById("navbar-collapse");
    const navbarSearchButton = document.getElementById("navbar-search-button");
    const navbarContainer = document.getElementById("navbar-container");
    const navbarSearchInput = document.getElementById("navbar-search-title");
    const navbarSearchPageCache = document.getElementById("navbar-search-cache-page");
    const dropdownIcon = document.getElementById("navbar-dropdown-icon");
    const userButton = document.getElementById("user-button");

    const originalSearchValue = navbarSearchInput.value;
    const originalSearchPage = navbarSearchPageCache != null ? navbarSearchPageCache.value : 1;

    navbarSearchInput.addEventListener("input", e => {
        if (navbarSearchPageCache == null) return;

        if (navbarSearchInput.value !== originalSearchValue) {
            navbarSearchPageCache.value = 1;
        } else {
            navbarSearchPageCache.value = originalSearchPage;
        }
    });

    navbarSearchButton.addEventListener("click", e => {
       window.location.href = "/katzegorien";
    });

    window.addEventListener('resize', e => handleNavbarResizing(e));
    document.addEventListener("DOMContentLoaded", e => handleNavbarResizing(e));

    if (userButton != null) {
        userButton.addEventListener("click", e => {
            let width = window.innerWidth || document.documentElement.clientWidth;

            if (width < resizeNavMaxWidth) {
                window.location.href = "/user";
            }
        });
    }

    navbarButton.addEventListener("click", e => {
        if (dropdownIcon.classList.contains("fa-x")) {
            collapsedState();
        } else if (dropdownIcon.classList.contains("fa-bars")) {
            expandedState();
        }
    });

    function handleNavbarResizing(e) {
        let width = window.innerWidth || document.documentElement.clientWidth;

        if (lastWidth === width) return;

        if (width < resizeNavMaxWidth) {
            navbarButton.classList.add("collapsed");
            navbarCollapse.classList.add("collapsed");
            collapsedState();
        } else {
            navbarButton.classList.remove("collapsed");
            navbarCollapse.classList.remove("collapsed");
            expandedState();
            navbarContainer.classList.add("collapsed");
        }

        lastWidth = width;
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
