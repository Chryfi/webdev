<?php

$request = $_GET['p'];
$reqComponents = explode('/', $request);
$page = array_pop($reqComponents);
$dir = implode('/', $reqComponents) . (count($reqComponents) != 0 ? "/" : "");

if (empty($page)) {
    $page = "index";
}

$pageFileName = pathinfo($page, PATHINFO_FILENAME);


$headFiletype = nextFileType("content/heads/".$dir.$pageFileName, ["html", "php"]);
$headFile = !$headFiletype ? "index.html" : $pageFileName.".".$headFiletype;
$headPath = "content/heads/".$dir.$headFile;

if (file_exists($headPath)) {
    include("content/heads/".$dir.$headFile);
} else {
    include("content/heads/index.html");
}


include("content/defaults/header.php");

$pageFileType = pathinfo($page, PATHINFO_EXTENSION);
$pageFileType = !empty($pageFileType) ? $pageFileType : nextFileType("content/pages/".$dir.$pageFileName, ["html", "php"]);

if (file_exists("content/pages/".$dir.$pageFileName.".".$pageFileType)) {
    include("content/pages/".$dir.$pageFileName.".".$pageFileType);
}
else {
    $_POST["error_code"] = 404;
    $_POST["error_description"] = "Page not found!";
    include("content/pages/error_message.php");
}

include("content/defaults/footer.php");


/**
 * @return string|boolean returns the first found filetype in the path. If nothing was found returns false.
 */
function nextFileType($path, $filetypes) {
    for ($i = 0; $i < count($filetypes); $i++) {
        $filetype = $filetypes[$i];

        if (file_exists($path.".".$filetype)) {
            return $filetype;
        } else if ($i == count($filetypes) - 1) {
            return false;
        }
    }
}
?>