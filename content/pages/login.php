<?php

require_once(BASE_PATH . "/src/datalayer/tables/user.php");
require_once(BASE_PATH . "/src/application/userForm.php");
require_once(BASE_PATH . "/src/application/sessionFunctions.php");

/* quick access for output */
$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";

/* error messages for the input names */
$errors = [];

if (isset($_POST["username"])) {
    $requiredPosts = ["username", "password"];

    foreach ($requiredPosts as $key) {
        if (!isset($_POST[$key]) || $_POST[$key] == "") {
            $errors[$key] = "Geben Sie etwas ein.";
        }
    }

    if (count($errors) == 0) {
        $userTable = new UserTable(getKatzenBlogDatabase());
        if (!usernameExists($username) || !verifyPassword($user = $userTable->getByUserName($username), $password)) {
            $errors["general"] = "Username oder Passwort falsch.";
        } else {
            login($user);

            /*
             * The header is already loaded and shows the buttons to login.
             * After this block, the user will be logged in, but without refreshing the page, the header will be outdated.
             * We can't use PHP header() because of the routing system.
             */
            echo '<script type="text/javascript">
                window.location.href = \'./\';
            </script>';
        }
    }
}

function verifyPassword(User $user, string $password) : bool {
    return password_verify($password, $user->getPassword());
}
?>

<div class="page-wrapper">
    <main class="container-sm display-flex justify-content-center">
        <div class="round-container register-container col-xl-6 w-100">
            <?php if (!isLoggedin()): ?>
            <h2 class="h2">Login</h2>
            <form class="register-form" method="POST">
                <div class="row">
                    <?php outputError("general", $errors);?>
                </div>
                <div class="row">
                    <div class="col">
                        <p class="lead">Username</p>
                        <input class="input" type="text" name="username" value="<?php echo $username;?>">
                    </div>
                    <div class="row">
                        <?php outputError("username", $errors);?>
                    </div>
                </div>
                <div class="row register-gap-row">
                    <div class="col-md">
                        <p class="lead">Passwort</p>
                        <input class="input" type="password" name="password" value="<?php echo $password;?>">
                    </div>
                    <div class="row">
                        <?php outputError("password", $errors);?>
                    </div>
                </div>
                <div class="row submit-row justify-content-center">
                    <div class="col-auto">
                        <button type="submit" class="button button-primary button-dark">Login</button>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <h2 class="h2">Willkommen, <?php echo getSessionUsername(); ?></h2>
            <?php endif; ?>
        </div>
    </main>
</div>