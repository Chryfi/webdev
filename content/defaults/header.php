<nav class="navbar">
    <ul class="navbar-list">
        <li class="navbar-item">
            <a href="index" class="navbar-link <?php if(empty($_GET["p"]) || $_GET["p"] == "index") echo "active";?>">Home</a>
        </li>
        <li class="navbar-item">
            <a href="katzegorien" class="navbar-link <?php if($_GET["p"] == "katzegorien") echo "active";?>">Katzegorien</a>
        </li>
    </ul>
    <div class="row align-items-center justify-content-space-between navbar-row">
        <div class="col-auto">
            <input type="text" class="input" placeholder="Suche...">
        </div>
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
    </div>
</nav>