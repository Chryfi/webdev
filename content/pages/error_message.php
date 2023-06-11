<div class="page-wrapper">
    <header class="container-sm">
        <div class="header-text">
            <h1>Error <?php echo ($_POST["error_code"] ?? "Error ..."); ?></h1>
            <p><?php echo ($_POST["error_description"] ?? ""); ?></p>
        </div>
    </header>
</div>