<?php
require_once(BASE_PATH . "/src/datalayer/tables/user.php");

/**
 * Common functions used in register and login process
 */

function usernameExists($username): bool {
    $db = getKatzenBlogDatabase();
    $userTable = new UserTable($db);
    $result = $userTable->getByUserName($username) != null;
    $db->disconnect();

    return $result;
}

function emailExists($email): bool {
    $db = getKatzenBlogDatabase();
    $userTable = new UserTable($db);
    $result = $userTable->getByEmail($email) != null;
    $db->disconnect();

    return $result;
}

function outputError($key, $errors) {
    if (isset($errors[$key])) {
        echo '<p class="error"><i class="error fa-solid fa-circle-exclamation"></i> '.$errors[$key].'</p>';
    }
}
?>