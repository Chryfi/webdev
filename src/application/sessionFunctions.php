<?php
require_once (BASE_PATH."/src/datalayer/tables/user.php");

function login(User $user) {
    if (isLoggedin()) return;

    $_SESSION["id"] = session_id();
    $_SESSION["user"]["userid"] = $user->getId();
    $_SESSION["user"]["username"] = $user->getUsername();
}

function logout() {
    if (!isLoggedin()) return;

    session_destroy();
    unset($_SESSION);
}

function getSessionUsername() : ?string {
    return isLoggedin() ? $_SESSION["user"]["username"] : null;
}

function isLoggedin() : bool {
    return isset($_SESSION["user"]["userid"]);
}

/**
 * @return User|null
 * @throws PDOException
 */
function getLoggedInUser() : ?User {
    if (!isLoggedin()) return null;

    $userTable = new UserTable(getKatzenBlogDatabase());
    return $userTable->getById($_SESSION["user"]["userid"]);
}