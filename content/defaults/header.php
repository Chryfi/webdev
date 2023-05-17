<nav class="navbar">
    <ul class="navbar-list">
        <li class="navbar-item">
            <a href="index" class="navbar-link <?php if(empty($_GET["p"]) || $_GET["p"] == "index") echo "active";?>">Home</a>
        </li>
        <li class="navbar-item">
            <a href="katzegorien" class="navbar-link <?php if($_GET["p"] == "katzegorien") echo "active";?>">Katzegorien</a>
        </li>
        <li class="navbar-item">
            <a href="registrieren" class="navbar-link <?php if($_GET["p"] == "registrieren") echo "active";?>">Registrieren</a>
        </li>
    </ul>
    <div class="search-container">
        <input type="text" class="search-input" placeholder="Search...">
        <button class="search-button display-flex align-items-center justify-content-center"><img class="icon" src="/ressources/icons/magnifying-glass-solid.svg"></button>
    </div>
</nav>