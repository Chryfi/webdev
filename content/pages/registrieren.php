<?php

require_once (BASE_PATH."/src/datalayer/user.php");

/* quick access for output */
$username = $_POST["username"] ?? "";
$firstname = $_POST["firstname"] ?? "";
$surname = $_POST["surname"] ?? "";
$email = $_POST["email"] ?? "";
$birthday = $_POST["birthday"] ?? "";

/* error messages for the input names */
$errors = [];
$registered = false;

if (isset($_POST["username"])) {
    $requiredPosts = ["username", "firstname", "surname", "surname", "email", "password", "password-repeated", "birthday"];

    foreach ($requiredPosts as $key) {
        if (!isset($_POST[$key]) || $_POST[$key] == "") {
            $errors[$key] = "Geben Sie etwas ein.";
        } else if ($errorMsg = validateUserData($key, $_POST[$key])) {
            $errors[$key] = $errorMsg;
        }
    }

    if (count($errors) == 0) {
        if ($_POST["password"] != $_POST["password-repeated"]) {
            $errors["password"] = "Die Passwörter stimmen nicht überein.";
            $errors["password-repeated"] = $errors["password"];
        } else {
            $registered = registerUser($_POST["username"], $_POST["password"], $_POST["email"], $_POST["firstname"], $_POST["surname"], $_POST["birthday"]);
        }
    }
}

function registerUser($username, $password, $email, $firstname, $surname, $birthday): bool {
    $userTable = new UserTable(getKatzenBlogDatabase());
    $user = new User(null, $username, $password, $email, $firstname, $surname, $birthday);

    return $userTable->insertUser($user);
}

/**
 * @param $key
 * @param $value
 * @return string|null returns an error message for the specific input type. If null, it means everything is okay.
 */
function validateUserData($key, $value) : ?string {
    switch ($key) {
        case "email":
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return "Das Email Format ist falsch.";
            } else if (emailExists($value)) {
                return "Die Email ist bereits registriert.";
            }
            break;
        case "password":
            if (strlen($value) < 8) {
                return "Das Passwort hat weniger als 8 Zeichen";
            }
            break;
        case "username":
            if (usernameExists($value)) {
                return "Der Username ist bereits vergeben";
            }
            break;
    }

    return null;
}

function usernameExists($username): bool {
    $userTable = new UserTable(getKatzenBlogDatabase());
    return $userTable->getByUserName($username) != null;
}

function emailExists($email): bool {
    $userTable = new UserTable(getKatzenBlogDatabase());
    return $userTable->getByEmail($email) != null;
}

function outputError($key, $errors) {
    if (array_key_exists($key, $errors)) {
        echo '<p class="error"><i class="error fa-solid fa-circle-exclamation"></i> '.$errors[$key].'</p>';
    }
}
?>

<div class="page-wrapper">
    <main class="container-sm display-flex justify-content-center">
        <div class="round-container register-container col-xl-6 w-100">
            <?php if (!$registered): ?>
            <h2 class="h2">Registrierung</h2>
            <form id="register-form" class="register-form" method="POST">
                <div class="row">
                    <div class="col">
                        <p class="lead">Username</p>
                        <input class="input" type="text" name="username" value="<?php echo $username; ?>">
                    </div>
                    <div class="row">
                        <?php outputError("username", $errors);?>
                    </div>
                </div>

                <div class="row register-gap-row">
                    <div class="col-md">
                        <p class="lead">Vorname</p>
                        <input class="input" type="text" name="firstname" value="<?php echo $firstname; ?>">
                    </div>
                    <div class="col-md">
                        <p class="lead">Nachname</p>
                        <input class="input" type="text" name="surname" value="<?php echo $surname; ?>">
                    </div>
                    <div class="row register-gap-row">
                        <div class="col-md">
                            <?php outputError("firstname", $errors);?>
                        </div>
                        <div class="col-md">
                            <?php outputError("surname", $errors);?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <p class="lead">E-Mail</p>
                        <input class="input" type="email" name="email" value="<?php echo $email; ?>">
                    </div>
                    <div class="row">
                        <?php outputError("email", $errors);?>
                    </div>
                </div>
                <div class="row register-gap-row">
                    <div class="col-md">
                        <p class="lead">Passwort</p>
                        <input class="input" type="password" name="password">
                    </div>
                    <div class="col-md">
                        <p class="lead">Passwort wiederholen</p>
                        <input class="input" type="password" name="password-repeated">
                    </div>
                    <div class="row register-gap-row">
                        <div class="col-md">
                            <?php outputError("password", $errors);?>
                        </div>
                        <div class="col-md">
                            <?php outputError("password-repeated", $errors);?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <p class="lead">Geburtstag</p>
                        <input type="date" class="input input-date" name="birthday" value="<?php echo $birthday; ?>">
                    </div>
                    <div class="row">
                        <?php outputError("birthday", $errors);?>
                    </div>
                </div>
                <div class="row submit-row justify-content-center">
                    <div class="col-auto">
                        <button type="submit" class="button button-primary button-dark">Registrieren</button>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <h1 class="h1">Registrierung erfolgreich!</h1>
            <?php endif; ?>
        </div>
    </main>
</div>