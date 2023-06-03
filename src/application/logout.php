<?php
require_once (BASE_PATH."/src/application/sessionFunctions.php");

logout();

if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else if (isset($_GET["redirect"])) {
    header("Location: " . $_GET["redirect"]);
} else {
    header("Location: /");
}
exit;
?>