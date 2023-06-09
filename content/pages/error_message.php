<div class="page-wrapper">
    <header class="container-sm">
        <div class="header-text">
            <h1>Error <?php if (isset($_POST["error_code"])) echo $_POST["error_code"]; ?></h1>
            <p><?php if (isset($_POST["error_description"])) echo $_POST["error_description"]; ?></p>
        </div>
    </header>
</div>