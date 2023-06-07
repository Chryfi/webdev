<?php
require_once ("requestParser.php");

/* to avoid endless and annoying relative paths */
define('BASE_PATH', str_replace(["/index.php", "\index.php", "\\"], ["", "", "/"], __FILE__));

$_requestParser = new RequestParser($_GET["p"]);
$_requestParser->parse();

session_start();

/* request to php source files should be let through */
if ($_requestParser->getRequestPart(0) == "src") {
    include($_requestParser->getRequest());
    return;
}

/**
 * If the user tries to enter a directory that doesn't exist in "heads",
 * the default head file from the base directory needs to be used.
 * "content/heads/index.html" MUST ALWAYS EXIST!
 */
if (file_exists($_requestParser->getHeadPath())) {
    include($_requestParser->getHeadPath());
}
else {
    include("content/heads/index.html");
}

include("content/defaults/header.php");

if (file_exists($_requestParser->getPagePath())) {
    include($_requestParser->getPagePath());
}
else {
    $_POST["error_code"] = 404;
    $_POST["error_description"] = "Page not found!";
    include("content/pages/error_message.php");
}

include("content/defaults/footer.php");
?>