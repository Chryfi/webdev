<?php
require_once(BASE_PATH . "/src/datalayer/tables/user.php");
require_once(BASE_PATH . "/src/utils/userForm.php");

/* quick access for output */
$username = $_POST["username"] ?? "";
$firstname = $_POST["firstname"] ?? "";
$surname = $_POST["surname"] ?? "";
$email = $_POST["email"] ?? "";
$birthday = $_POST["birthday"] ?? "";
$password = $_POST["password"] ?? "";
$passwordRepeated = $_POST["password-repeated"] ?? "";

/* error messages for the input names */
$errors = [];
$registered = false;

if (isset($_POST["save"])) {
    $requiredPosts = ["username", "firstname", "surname", "surname", "email", "password", "password-repeated", "birthday"];

    /* input validation (also validating if username email etc. already exist) */
    foreach ($requiredPosts as $key) {
        if (!isset($_POST[$key]) || $_POST[$key] == "") {
            $errors[$key] = "Geben Sie etwas ein.";
        } else if ($errorMsg = validateUserData($key, $_POST[$key])) {
            $errors[$key] = $errorMsg;
        }
    }

    if (!isset($errors["password"]) && $_POST["password"] != $_POST["password-repeated"]) {
        $errors["password"] = "Die Passwörter stimmen nicht überein.";
        $errors["password-repeated"] = $errors["password"];
    }

    if (count($errors) == 0) {
        $registered = registerUser($_POST["username"], $_POST["password"], $_POST["email"], $_POST["firstname"], $_POST["surname"], $_POST["birthday"]);
    }
}

function registerUser($username, $password, $email, $firstname, $surname, $birthday): bool {
    $db = getKatzenBlogDatabase();
    $userTable = new UserTable($db);
    $user = User::createNecessary($username, password_hash($password, PASSWORD_BCRYPT), $email, $firstname, $surname, $birthday);

    $result = $userTable->insertUser($user);

    $db->disconnect();

    return $result;
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
?>

<div class="page-wrapper">
    <header class="container-sm">
        <div class="header-text">
            <h1>Registrierung</h1>
            <p class="lead">Erstelle deinen Account.</p>
        </div>
    </header>
    <main class="container-sm">
        <div class="round-container register-container col-xl-6">
            <?php if (!$registered): ?>
            <form id="register-form" class="register-form" method="POST">
                <div class="row">
                    <div class="col">
                        <p class="lead">Username</p>
                        <input class="input" type="text" name="username" value="<?php echo $username; ?>">
                        <?php outputError("username", $errors);?>
                    </div>
                </div>
                <div class="row register-gap-row">
                    <div class="col-md">
                        <p class="lead">Vorname</p>
                        <input class="input" type="text" name="firstname" value="<?php echo $firstname; ?>">
                        <?php outputError("firstname", $errors);?>
                    </div>
                    <div class="col-md">
                        <p class="lead">Nachname</p>
                        <input class="input" type="text" name="surname" value="<?php echo $surname; ?>">
                        <?php outputError("surname", $errors);?>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <p class="lead">E-Mail</p>
                        <input class="input" type="email" name="email" value="<?php echo $email; ?>">
                        <?php outputError("email", $errors);?>
                    </div>
                </div>
                <div class="row register-gap-row">
                    <div class="col-md">
                        <p class="lead">Passwort</p>
                        <input class="input" type="password" name="password" value="<?php echo $password; ?>">
                        <?php outputError("password", $errors);?>
                    </div>
                    <div class="col-md">
                        <p class="lead">Passwort wiederholen</p>
                        <input class="input" type="password" name="password-repeated" value="<?php echo $passwordRepeated; ?>">
                        <?php outputError("password-repeated", $errors);?>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <p class="lead">Geburtstag</p>
                        <input type="date" class="input input-date" name="birthday" value="<?php echo $birthday; ?>">
                        <?php outputError("birthday", $errors);?>
                    </div>
                </div>
                <div class="row submit-row justify-content-center">
                    <div class="col-auto">
                        <button type="submit" class="button button-primary button-dark" name="save">Registrieren</button>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <p class="lead text-center"><i class="fa-solid fa-circle-check"></i> Registrierung erfolgreich!</p>
            <?php endif; ?>
        </div>
    </main>
</div>