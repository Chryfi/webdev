<?php
require_once(BASE_PATH . "/src/datalayer/tables/user.php");

/**
 * Common functions used in register and login process
 */

function usernameExists($username): bool {
    $userTable = new UserTable(getKatzenBlogDatabase());
    return $userTable->getByUserName($username) != null;
}

function emailExists($email): bool {
    $userTable = new UserTable(getKatzenBlogDatabase());
    return $userTable->getByEmail($email) != null;
}

function outputError($key, $errors) {
    if (isset($errors[$key])) {
        echo '<p class="error"><i class="error fa-solid fa-circle-exclamation"></i> '.$errors[$key].'</p>';
    }
}
?>