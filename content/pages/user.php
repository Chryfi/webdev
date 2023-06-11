<?php
require_once(BASE_PATH . "/src/utils/sessionFunctions.php");

$user = getLoggedInUser();

?>

<div class="page-wrapper">
    <header class="container-sm">
        <div class="header-text">
            <?php if (isLoggedin()): ?>
                <h1>Willkommen <?php echo getSessionUsername(); ?></h1>
                <p class="lead">Hier kannst du deine Accountdaten einsehen.</p>
            <?php else:?>
                <h1>Melde dich zuerst an oder erstelle einen Account.</h1>
            <?php endif;?>
        </div>
    </header>
    <main class="container-sm display-flex justify-content-center">
        <?php if (isLoggedin()): ?>
            <div class="round-container register-form col-xl-6">
                <div class="row">
                    <div class="col">
                        <p class="lead">Username</p>
                        <input class="input" type="text" value="<?php echo $user->getUsername(); ?>" disabled>
                    </div>
                </div>
                <div class="row register-gap-row">
                    <div class="col-md">
                        <p class="lead">Vorname</p>
                        <input class="input" type="text" value="<?php echo $user->getFirstname(); ?>" disabled>
                    </div>
                    <div class="col-md">
                        <p class="lead">Nachname</p>
                        <input class="input" type="text" value="<?php echo $user->getSurname(); ?>" disabled>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col">
                        <p class="lead">Email</p>
                        <input class="input" type="text" value="<?php echo $user->getEmail(); ?>" disabled>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-5">
                        <p class="lead">Geburtstag</p>
                        <input class="input input-date" value="<?php echo date_format(date_create($user->getBirthday()), "d.m.Y"); ?>" disabled>
                    </div>
                </div>
            </div>
        <?php endif;?>
    </main>
</div>

