<?php
/* to avoid endless and annoying relative paths */
define('BASE_PATH', "C:/xampp/htdocs/KatzenBlog");

$request = $_GET['p'];
$reqComponents = explode('/', $request);
$page = array_pop($reqComponents);
$dir = implode('/', $reqComponents) . (count($reqComponents) != 0 ? "/" : "");

/* request to php source files should be let through */
if (count($reqComponents) != 0 && $reqComponents[0] == "src") {
    include($request);
    return;
}

if (empty($page)) {
    $page = "index";
}

$pageFileName = pathinfo($page, PATHINFO_FILENAME);
$pageFileType = pathinfo($page, PATHINFO_EXTENSION);
$pageFileType = !empty($pageFileType) ? $pageFileType : findFileType("content/pages/".$dir.$pageFileName, ["html", "php"]);
$pagePath = "content/pages/".$dir.$pageFileName.".".$pageFileType;

$headFiletype = findFileType("content/heads/".$dir.$pageFileName, ["html", "php"]);
$headFile = !$headFiletype ? "index.html" : $pageFileName.".".$headFiletype;
$headPath = "content/heads/".$dir.$headFile;

/**
 * If the user tries to enter a directory that doesn't exist in "heads",
 * the default head file from the base directory needs to be used.
 * "content/heads/index.html" MUST ALWAYS EXIST!
 */
if (file_exists($headPath)) {
    include($headPath);
}
else {
    include("content/heads/index.html");
}

include("content/defaults/header.php");

if ($pageFileType && file_exists($pagePath)) {
    include($pagePath);
}
else {
    $_POST["error_code"] = 404;
    $_POST["error_description"] = "Page not found!";
    include("content/pages/error_message.php");
}

include("content/defaults/footer.php");


/**
 * @return ?string returns the first found filetype in the path. If nothing was found returns NULL.
 */
function findFileType($path, $filetypes): ?string {
    for ($i = 0; $i < count($filetypes); $i++) {
        $filetype = $filetypes[$i];

        if (file_exists($path.".".$filetype)) {
            return $filetype;
        } else if ($i == count($filetypes) - 1) {
            return NULL;
        }
    }
}
?>