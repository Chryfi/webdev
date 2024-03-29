<?php

require_once(BASE_PATH . "/src/datalayer/tables/user.php");
require_once(BASE_PATH . "/src/utils/userForm.php");
require_once(BASE_PATH . "/src/utils/sessionFunctions.php");
require_once(BASE_PATH . "/src/utils/redirect.php");

/* quick access for output */
$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";

/* error messages for the input names */
$errors = [];

if (isset($_POST["login"]) && !isLoggedin()) {
    $requiredPosts = ["username", "password"];

    foreach ($requiredPosts as $key) {
        if (!isset($_POST[$key]) || $_POST[$key] == "") {
            $errors[$key] = "Gebe etwas ein.";
        }
    }

    if (count($errors) == 0) {
        $db = getKatzenBlogDatabase();
        $userTable = new UserTable($db);
        if (!($user = $userTable->getByUserName($username)) || !verifyPassword($user, $password)) {
            $errors["general"] = "Username oder Passwort falsch.";
            $db->disconnect();
        } else {
            $db->disconnect();
            login($user);

            /*
             * The header is already loaded and shows the buttons to login.
             * After this code, the user will be logged in, but without refreshing the page, the header will be outdated.
             * We can't use PHP header() because of the routing system.
             */
            refreshWithJS();
        }
    }
}

function verifyPassword(User $user, string $password) : bool {
    return password_verify($password, $user->getPassword());
}
?>

<div class="page-wrapper">
    <header class="container-sm">
        <div class="header-text">
            <?php if (!isLoggedin()): ?>
                <h1>Login</h1>
                <p class="lead">Logge dich in deinen Account ein.</p>
            <?php else: ?>
                <h1>Willkommen, <?php echo getSessionUsername(); ?></h1>
                <p class="lead">Du kannst deinen Account <a href="/user">hier</a> einsehen</p>
            <?php endif; ?>
        </div>
    </header>
    <main class="container-sm">
        <?php if (!isLoggedin()): ?>
            <div class="round-container register-container col-xl-6">
                <form class="register-form" method="POST">
                    <div class="row">
                        <?php outputError("general", $errors);?>
                    </div>
                    <div class="row">
                        <div class="col">
                            <p class="lead">Username</p>
                            <input class="input" type="text" name="username" value="<?php echo $username;?>">
                            <?php outputError("username", $errors);?>
                        </div>
                    </div>
                    <div class="row register-gap-row">
                        <div class="col-md">
                            <p class="lead">Passwort</p>
                            <input class="input" type="password" name="password" value="<?php echo $password;?>">
                            <?php outputError("password", $errors);?>
                        </div>
                    </div>
                    <div class="row submit-row justify-content-center">
                        <div class="col-auto">
                            <button type="submit" class="button button-primary button-dark" name="login">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>